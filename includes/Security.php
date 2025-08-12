<?php
/**
 * CLASSE DE SEGURANÇA DO SISTEMA POLIS
 * 
 * Esta classe centraliza todas as funcionalidades de segurança do sistema,
 * fornecendo métodos para proteger contra ataques comuns em aplicações web
 * 
 * Recursos de Segurança Implementados:
 * - Proteção CSRF (Cross-Site Request Forgery)
 * - Rate Limiting para tentativas de login
 * - Sanitização avançada de inputs
 * - Headers HTTP de segurança
 * - Sistema de auditoria e logs
 * 
 * Padrão Singleton implícito: Todos os métodos são estáticos
 * para garantir configuração consistente em toda a aplicação
 */

class Security {
    // ===== CONFIGURAÇÕES DE SESSÃO =====
    // Chaves usadas para armazenar dados de segurança na sessão PHP
    private static $sessionKeyCSRF = 'csrf_token';        // Token de proteção CSRF
    private static $sessionKeyAttempts = 'login_attempts'; // Contador de tentativas de login
    private static $sessionKeyLockout = 'lockout_time';    // Timestamp do bloqueio

    // ===== CONFIGURAÇÕES DE RATE LIMITING =====
    private static $maxAttempts = 5;        // Máximo 5 tentativas de login
    private static $lockoutTime = 900;      // 15 minutos de bloqueio (900 segundos)

    /**
     * SANITIZAÇÃO AVANÇADA DE INPUTS
     * 
     * Remove caracteres perigosos e valida dados de entrada
     * Suporta sanitização recursiva de arrays
     * 
     * @param mixed $input - Dado a ser sanitizado (string, array, etc.)
     * @param string $type - Tipo de sanitização ('string', 'email', 'int', 'float', 'url')
     * @return mixed - Dado sanitizado
     */
    public static function sanitizeInput($input, $type = 'string') {
        // ===== SANITIZAÇÃO RECURSIVA DE ARRAYS =====
        // Se o input for um array, aplica sanitização em cada elemento
        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                // Chama recursivamente para cada item do array
                return self::sanitizeInput($item, $type);
            }, $input);
        }

        // ===== SANITIZAÇÃO POR TIPO DE DADO =====
        switch ($type) {
            case 'email':
                // Remove caracteres inválidos de email, mantendo formato válido
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
                
            case 'int':
                // Mantém apenas dígitos e sinais de + e -
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                
            case 'float':
                // Mantém apenas dígitos, pontos decimais e sinais
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
            case 'url':
                // Remove caracteres que podem quebrar URLs
                return filter_var($input, FILTER_SANITIZE_URL);
                
            case 'string':
            default:
                // Escapa caracteres HTML para prevenir XSS
                // ENT_QUOTES: Converte aspas simples e duplas
                // UTF-8: Suporte completo ao português
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }

    /**
     * GERAÇÃO DE TOKEN CSRF
     * 
     * Cria um token único por sessão para proteger contra ataques CSRF
     * O token é gerado apenas uma vez por sessão e reutilizado
     * 
     * @return string - Token CSRF de 64 caracteres hexadecimais
     */
    public static function generateCSRFToken() {
        // Garante que a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Gera token apenas se não existe um na sessão
        if (!isset($_SESSION[self::$sessionKeyCSRF])) {
            // random_bytes(32) gera 32 bytes aleatórios criptograficamente seguros
            // bin2hex converte para hexadecimal (64 caracteres finais)
            $_SESSION[self::$sessionKeyCSRF] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION[self::$sessionKeyCSRF];
    }

    /**
     * VERIFICAÇÃO DE TOKEN CSRF
     * 
     * Valida se o token enviado pelo cliente corresponde ao armazenado na sessão
     * Usa hash_equals() para prevenir timing attacks
     * 
     * @param string $token - Token enviado pelo cliente
     * @return bool - true se token é válido, false caso contrário
     */
    public static function verifyCSRFToken($token) {
        // Garante que a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verifica se existe token na sessão E se coincide com o enviado
        // hash_equals() previne timing attacks comparando em tempo constante
        return isset($_SESSION[self::$sessionKeyCSRF]) && 
               hash_equals($_SESSION[self::$sessionKeyCSRF], $token);
    }

    /**
     * VERIFICAÇÃO DE RATE LIMITING
     * 
     * Sistema de proteção contra ataques de força bruta
     * Bloqueia IPs que excedem o limite de tentativas de login
     * 
     * @return array - ['blocked' => bool, 'remainingTime' => int, 'message' => string]
     */
    public static function checkLoginAttempts() {
        // Garante que a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Inicializa contador de tentativas se não existe
        if (!isset($_SESSION[self::$sessionKeyAttempts])) {
            $_SESSION[self::$sessionKeyAttempts] = 0;
        }

        // ===== VERIFICAÇÃO DE BLOQUEIO =====
        // Se ultrapassou o limite máximo de tentativas
        if ($_SESSION[self::$sessionKeyAttempts] >= self::$maxAttempts) {
            
            // Registra momento do primeiro bloqueio
            if (!isset($_SESSION[self::$sessionKeyLockout])) {
                $_SESSION[self::$sessionKeyLockout] = time();
            }
            
            // Calcula tempo restante de bloqueio
            $elapsedTime = time() - $_SESSION[self::$sessionKeyLockout];
            
            // Se ainda está dentro do período de lockout
            if ($elapsedTime < self::$lockoutTime) {
                $remainingTime = self::$lockoutTime - $elapsedTime;
                
                return [
                    'blocked' => true,
                    'remainingTime' => $remainingTime,
                    'message' => "Muitas tentativas de login. Tente novamente em " . ceil($remainingTime / 60) . " minutos."
                ];
            } else {
                // ===== RESET AUTOMÁTICO APÓS LOCKOUT =====
                // Período de bloqueio expirou, limpa contadores
                $_SESSION[self::$sessionKeyAttempts] = 0;
                unset($_SESSION[self::$sessionKeyLockout]);
            }
        }

        // Usuário não está bloqueado
        return ['blocked' => false];
    }

    /**
     * INCREMENTO DE TENTATIVAS FALHADAS
     * 
     * Registra uma tentativa de login mal-sucedida
     * Usado para acionar o sistema de rate limiting
     */
    public static function incrementLoginAttempts() {
        // Garante que a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Inicializa contador se não existe
        if (!isset($_SESSION[self::$sessionKeyAttempts])) {
            $_SESSION[self::$sessionKeyAttempts] = 0;
        }
        
        // Incrementa contador de tentativas falhadas
        $_SESSION[self::$sessionKeyAttempts]++;
    }

    /**
     * RESET DE TENTATIVAS (LOGIN BEM-SUCEDIDO)
     * 
     * Limpa todos os contadores quando login é bem-sucedido
     * Remove tanto o contador quanto o timestamp de lockout
     */
    public static function resetLoginAttempts() {
        // Garante que a sessão está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Zera contador de tentativas falhadas
        $_SESSION[self::$sessionKeyAttempts] = 0;
        
        // Remove timestamp de lockout (se existir)
        unset($_SESSION[self::$sessionKeyLockout]);
    }

    /**
     * CONFIGURAÇÃO DE HEADERS HTTP DE SEGURANÇA
     * 
     * Aplica headers de segurança modernos para proteger contra ataques comuns
     * Deve ser chamado antes de qualquer output HTML
     */
    public static function setSecurityHeaders() {
        // Previne MIME type sniffing attacks
        header('X-Content-Type-Options: nosniff');
        
        // Previne que a página seja exibida em frames/iframes (clickjacking)
        header('X-Frame-Options: DENY');
        
        // Ativa proteção XSS do navegador (legado, mas ainda útil)
        header('X-XSS-Protection: 1; mode=block');
        
        // Controla informações de referrer enviadas
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // ===== CONTENT SECURITY POLICY (CSP) =====
        // Define fontes confiáveis para diferentes tipos de recursos
        // Protege contra XSS e data injection attacks
        header('Content-Security-Policy: ' .
            "default-src 'self' https:; " .                                    // Padrão: apenas HTTPS
            "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; " . // Scripts permitidos
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .  // CSS permitido
            "img-src 'self' data: https:; " .                                  // Imagens de qualquer HTTPS + data URLs
            "font-src 'self' https://fonts.gstatic.com;"                       // Fontes do Google Fonts
        );
    }

    /**
     * SISTEMA DE AUDITORIA E LOGS
     * 
     * Registra atividades importantes do sistema para análise de segurança
     * Cria logs estruturados com informações completas de contexto
     * 
     * @param string $action - Ação realizada (LOGIN_SUCCESS, DATA_ACCESS, etc.)
     * @param string $details - Detalhes específicos da ação
     * @param int|null $userId - ID do usuário (opcional, detecta automaticamente)
     */
    public static function logActivity($action, $details = '', $userId = null) {
        // ===== CONFIGURAÇÃO DO ARQUIVO DE LOG =====
        $logFile = __DIR__ . '/../logs/audit.log';  // Caminho para arquivo de auditoria
        $logDir = dirname($logFile);                 // Diretório dos logs
        
        // Cria diretório de logs se não existe
        if (!file_exists($logDir)) {
            // mkdir recursivo com permissões 755 (rwx r-x r-x)
            mkdir($logDir, 0755, true);
        }
        
        // ===== COLETA DE INFORMAÇÕES DE CONTEXTO =====
        $timestamp = date('Y-m-d H:i:s');                           // Data/hora atual
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';                 // IP do cliente
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';      // Navegador do cliente
        
        // Determina ID do usuário: parâmetro > sessão > 'anonymous'
        $userId = $userId ?? ($_SESSION['user_id'] ?? 'anonymous');
        
        // ===== FORMATAÇÃO DA ENTRADA DE LOG =====
        // Formato: [timestamp] User: ID | IP: xxx | Action: xxx | Details: xxx | UserAgent: xxx
        $logEntry = "[$timestamp] User: $userId | IP: $ip | Action: $action | Details: $details | UserAgent: $userAgent" . PHP_EOL;
        
        // ===== GRAVAÇÃO SEGURA NO ARQUIVO =====
        // FILE_APPEND: Adiciona ao final do arquivo (não substitui)
        // LOCK_EX: Trava exclusiva para evitar corrupção em acessos simultâneos
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}

?>