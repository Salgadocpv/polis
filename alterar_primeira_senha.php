<?php
/**
 * ===== PÁGINA DE ALTERAÇÃO DE PRIMEIRA SENHA =====
 * 
 * Esta página é exibida quando um usuário faz login pela primeira vez
 * e precisa alterar sua senha padrão (CPF) para uma senha personalizada.
 * 
 * Fluxo de Segurança:
 * - Verifica sessão temporária de primeiro login
 * - Força criação de nova senha forte
 * - Atualiza banco de dados removendo flag de primeira_senha
 * - Cria sessão completa após troca bem-sucedida
 * - Redireciona para dashboard apropriado
 */

// ===== VERIFICAÇÃO DE SESSÃO TEMPORÁRIA =====
session_start();

// Verifica se usuário chegou aqui através do fluxo correto de primeiro login
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['first_login'])) {
    // Se não tem sessão temporária, redireciona para login
    header('Location: index.html');
    exit();
}

// Extrai dados da sessão temporária
$temp_user_id = $_SESSION['temp_user_id'];
$temp_username = $_SESSION['temp_username'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Primeira Senha - Polis Engenharia</title>
    
    <!-- ===== IMPORTS DE FONTES E ESTILOS ===== -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- ===== SCRIPTS DO SISTEMA ===== -->
    <script src="assets/js/toast-system.js"></script>
    <script src="assets/js/modal-system.js"></script>
    
    <style>
        /* ===== ESTILOS ESPECÍFICOS DA PÁGINA ===== */
        body {
            background: linear-gradient(135deg, var(--cor-principal) 0%, var(--cor-secundaria) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            padding: 1rem;
        }

        .change-password-container {
            background: white;
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .logo-section {
            margin-bottom: 2rem;
        }

        .logo-section i {
            font-size: 4rem;
            color: var(--cor-vibrante);
            margin-bottom: 1rem;
        }

        .logo-section h1 {
            color: var(--cor-principal);
            margin: 0 0 0.5rem 0;
            font-size: 1.8rem;
            font-weight: 700;
        }

        .logo-section p {
            color: var(--cor-secundaria);
            margin: 0;
            font-size: 0.9rem;
        }

        .welcome-message {
            background: linear-gradient(135deg, var(--cor-vibrante), var(--cor-clara));
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        .welcome-message h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.3rem;
        }

        .welcome-message p {
            margin: 0;
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--cor-principal);
            font-size: 0.95rem;
        }

        .password-input-wrapper {
            position: relative;
        }

        .form-group input[type="password"] {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            padding-right: 3rem;
        }

        .form-group input[type="password"]:focus {
            outline: none;
            border-color: var(--cor-vibrante);
            box-shadow: 0 0 0 3px rgba(0, 180, 216, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--cor-secundaria);
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }

        .toggle-password:hover {
            color: var(--cor-vibrante);
        }

        .password-requirements {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 0.5rem;
        }

        .password-requirements h4 {
            margin: 0 0 0.5rem 0;
            font-size: 0.85rem;
            color: var(--cor-principal);
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .requirement.valid {
            color: #28a745;
        }

        .requirement i {
            width: 12px;
        }

        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--cor-vibrante), var(--cor-clara));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 180, 216, 0.3);
        }

        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn.loading {
            opacity: 0.7;
            cursor: not-allowed;
            position: relative;
        }

        .btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: translate(-50%, -50%) rotate(360deg); }
        }

        .logout-link {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
        }

        .logout-link a {
            color: var(--cor-secundaria);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .logout-link a:hover {
            color: var(--cor-vibrante);
        }

        /* ===== RESPONSIVIDADE ===== */
        @media (max-width: 768px) {
            .change-password-container {
                padding: 2rem;
                margin: 1rem;
            }
            
            .logo-section i {
                font-size: 3rem;
            }
            
            .logo-section h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="change-password-container">
        <!-- ===== SEÇÃO DO LOGO ===== -->
        <div class="logo-section">
            <i class="fas fa-shield-alt"></i>
            <h1>Polis Engenharia</h1>
            <p>Sistema de Gestão</p>
        </div>

        <!-- ===== MENSAGEM DE BOAS-VINDAS ===== -->
        <div class="welcome-message">
            <h2>Bem-vindo, <?php echo htmlspecialchars($temp_username); ?>!</h2>
            <p>Por segurança, você precisa alterar sua senha padrão antes de acessar o sistema.</p>
        </div>

        <!-- ===== FORMULÁRIO DE TROCA DE SENHA ===== -->
        <form id="changePasswordForm">
            <div class="form-group">
                <label for="current_password">Senha Atual (seu CPF)</label>
                <div class="password-input-wrapper">
                    <input type="password" id="current_password" name="current_password" 
                           placeholder="Digite seu CPF (apenas números)" required>
                    <button type="button" class="toggle-password" data-target="current_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="new_password">Nova Senha</label>
                <div class="password-input-wrapper">
                    <input type="password" id="new_password" name="new_password" 
                           placeholder="Digite sua nova senha" required>
                    <button type="button" class="toggle-password" data-target="new_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <!-- ===== REQUISITOS DE SENHA ===== -->
                <div class="password-requirements">
                    <h4>Requisitos da senha:</h4>
                    <div class="requirement" id="req-length">
                        <i class="fas fa-times"></i> Pelo menos 6 caracteres
                    </div>
                    <div class="requirement" id="req-uppercase">
                        <i class="fas fa-times"></i> Uma letra maiúscula
                    </div>
                    <div class="requirement" id="req-lowercase">
                        <i class="fas fa-times"></i> Uma letra minúscula
                    </div>
                    <div class="requirement" id="req-number">
                        <i class="fas fa-times"></i> Um número
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar Nova Senha</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Digite a nova senha novamente" required>
                    <button type="button" class="toggle-password" data-target="confirm_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" id="submitButton" class="btn" disabled>
                Alterar Senha e Entrar
            </button>
        </form>

        <!-- ===== LINK DE LOGOUT ===== -->
        <div class="logout-link">
            <a href="javascript:void(0)" onclick="logout()">
                <i class="fas fa-sign-out-alt"></i> Cancelar e Sair
            </a>
        </div>
    </div>

    <script>
        // ===== INICIALIZAÇÃO DA PÁGINA =====
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('changePasswordForm');
            const currentPasswordInput = document.getElementById('current_password');
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const submitButton = document.getElementById('submitButton');

            // ===== TOGGLE DE VISUALIZAÇÃO DE SENHA =====
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.className = 'fas fa-eye-slash';
                    } else {
                        input.type = 'password';
                        icon.className = 'fas fa-eye';
                    }
                });
            });

            // ===== VALIDAÇÃO EM TEMPO REAL DA SENHA =====
            newPasswordInput.addEventListener('input', function() {
                validatePasswordRequirements(this.value);
                checkFormValidity();
            });

            confirmPasswordInput.addEventListener('input', function() {
                checkFormValidity();
            });

            currentPasswordInput.addEventListener('input', function() {
                checkFormValidity();
            });

            // ===== FUNÇÃO DE VALIDAÇÃO DOS REQUISITOS =====
            function validatePasswordRequirements(password) {
                // Verificar comprimento
                updateRequirement('req-length', password.length >= 6);
                
                // Verificar letra maiúscula
                updateRequirement('req-uppercase', /[A-Z]/.test(password));
                
                // Verificar letra minúscula
                updateRequirement('req-lowercase', /[a-z]/.test(password));
                
                // Verificar número
                updateRequirement('req-number', /[0-9]/.test(password));
            }

            // ===== ATUALIZAR VISUAL DOS REQUISITOS =====
            function updateRequirement(reqId, isValid) {
                const element = document.getElementById(reqId);
                const icon = element.querySelector('i');
                
                if (isValid) {
                    element.classList.add('valid');
                    icon.className = 'fas fa-check';
                } else {
                    element.classList.remove('valid');
                    icon.className = 'fas fa-times';
                }
            }

            // ===== VERIFICAR VALIDADE GERAL DO FORMULÁRIO =====
            function checkFormValidity() {
                const currentPassword = currentPasswordInput.value;
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                // Verificar se todos os campos estão preenchidos
                const fieldsNotEmpty = currentPassword && newPassword && confirmPassword;
                
                // Verificar requisitos da senha
                const passwordRequirements = 
                    newPassword.length >= 6 &&
                    /[A-Z]/.test(newPassword) &&
                    /[a-z]/.test(newPassword) &&
                    /[0-9]/.test(newPassword);
                
                // Verificar se senhas coincidem
                const passwordsMatch = newPassword === confirmPassword;
                
                // Verificar se nova senha é diferente da atual
                const passwordsDifferent = currentPassword !== newPassword;
                
                // Habilitar botão apenas se tudo estiver válido
                submitButton.disabled = !(fieldsNotEmpty && passwordRequirements && passwordsMatch && passwordsDifferent);
                
                // Feedback visual para confirmação de senha
                if (confirmPassword) {
                    confirmPasswordInput.style.borderColor = passwordsMatch ? '#28a745' : '#dc3545';
                }
            }

            // ===== ENVIO DO FORMULÁRIO =====
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                setButtonLoading(true);
                
                const formData = {
                    current_password: currentPasswordInput.value,
                    new_password: newPasswordInput.value,
                    confirm_password: confirmPasswordInput.value
                };
                
                try {
                    const response = await fetch('api/alterar_primeira_senha.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok && result.success) {
                        Modal.success('Sucesso!', 'Senha alterada com sucesso! Redirecionando...').then(() => {
                            window.location.href = result.redirect || 'dashboard.php';
                        });
                    } else {
                        Modal.error('Erro', result.message || 'Erro ao alterar senha.');
                    }
                    
                } catch (error) {
                    console.error('Erro:', error);
                    Modal.error('Erro', 'Erro de conexão. Tente novamente.');
                } finally {
                    setButtonLoading(false);
                }
            });

            // ===== FUNÇÃO DE LOADING NO BOTÃO =====
            function setButtonLoading(loading) {
                if (loading) {
                    submitButton.disabled = true;
                    submitButton.classList.add('loading');
                    submitButton.textContent = 'Alterando...';
                } else {
                    submitButton.classList.remove('loading');
                    submitButton.textContent = 'Alterar Senha e Entrar';
                    checkFormValidity(); // Revalida para habilitar/desabilitar
                }
            }
        });

        // ===== FUNÇÃO DE LOGOUT =====
        function logout() {
            Modal.confirm(
                'Cancelar Alteração', 
                'Tem certeza que deseja cancelar? Você será desconectado do sistema.'
            ).then(() => {
                window.location.href = 'api/logout.php';
            }).catch(() => {
                // Usuário cancelou
            });
        }
    </script>
</body>
</html>