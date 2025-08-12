<?php
/**
 * SCRIPT PARA PREPARAR ARQUIVOS PARA DEPLOY MANUAL
 * 
 * Este script cria um ZIP com todos os arquivos necessários
 * para upload manual na Hostinger via File Manager
 */

$projectRoot = __DIR__;
$deployDir = $projectRoot . '/deploy-temp';
$zipFile = $projectRoot . '/polis-deploy.zip';

// Remove diretório temporário se existir
if (is_dir($deployDir)) {
    removeDirectory($deployDir);
}

// Cria diretório temporário
mkdir($deployDir, 0777, true);

echo "🚀 Preparando arquivos para deploy manual...\n";

// Lista de arquivos/pastas para incluir no deploy
$filesToCopy = [
    'api/',
    'assets/',
    'img/',
    'includes/',
    'listas/',
    'registros/',
    'sql/',
    'icons/',
    'dashboard.php',
    'index.html',
    'login.html',
    'manifest.json',
    'service-worker.js',
    'alterar_primeira_senha.php',
    'setup.php',
    '.htaccess'
];

// Copia arquivos necessários
foreach ($filesToCopy as $item) {
    $source = $projectRoot . '/' . $item;
    $destination = $deployDir . '/' . $item;
    
    if (file_exists($source)) {
        if (is_dir($source)) {
            copyDirectory($source, $destination);
            echo "✅ Copiado diretório: $item\n";
        } else {
            $destDir = dirname($destination);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            copy($source, $destination);
            echo "✅ Copiado arquivo: $item\n";
        }
    } else {
        echo "⚠️  Não encontrado: $item\n";
    }
}

// Cria o arquivo de conexão exemplo
$conexaoExemplo = $deployDir . '/api/conexao.php';
$conexaoTemplate = $projectRoot . '/api/conexao.exemplo.php';

if (file_exists($conexaoTemplate)) {
    copy($conexaoTemplate, $conexaoExemplo);
    echo "✅ Criado: api/conexao.php (LEMBRE-SE DE CONFIGURAR A SENHA!)\n";
}

// Cria arquivo README para deploy
$readme = $deployDir . '/LEIA-ME-DEPLOY.txt';
file_put_contents($readme, "
INSTRUÇÕES PARA DEPLOY MANUAL - POLIS ENGENHARIA
===============================================

1. UPLOAD DOS ARQUIVOS:
   - Acesse hPanel → File Manager
   - Navegue para /public_html/polis/
   - Faça upload de TODOS os arquivos desta pasta
   - Mantenha a estrutura de pastas

2. CONFIGURAR BANCO DE DADOS:
   - Edite o arquivo: api/conexao.php
   - Atualize a linha: define('DB_PASS', 'SUA_SENHA_REAL_AQUI');
   - Substitua 'SUA_SENHA_REAL_AQUI' pela senha real do banco

3. TESTAR O SISTEMA:
   - Acesse: https://seu-dominio.com/polis
   - Faça login ou teste as funcionalidades

4. PERMISSÕES (se necessário):
   - Pastas: 755
   - Arquivos PHP: 644
   - img/colaboradores/: 755

IMPORTANTE: 
- Delete este arquivo LEIA-ME-DEPLOY.txt após o deploy
- Não compartilhe a senha do banco de dados
");

echo "✅ Criado: LEIA-ME-DEPLOY.txt\n";

// Remove arquivos desnecessários do deploy
$filesToRemove = [
    'CLAUDE.md',
    'DEPLOY.md',
    '.deploy-trigger',
    '.gitignore',
    'db_functions.js',
    'generate_password_hash.php',
    'prepare-deploy.php'
];

foreach ($filesToRemove as $file) {
    $filePath = $deployDir . '/' . $file;
    if (file_exists($filePath)) {
        unlink($filePath);
        echo "🗑️  Removido: $file\n";
    }
}

echo "\n📦 Criando arquivo ZIP...\n";

// Cria o ZIP
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    addDirectoryToZip($zip, $deployDir, '');
    $zip->close();
    echo "✅ ZIP criado: polis-deploy.zip\n";
} else {
    echo "❌ Erro ao criar ZIP\n";
    exit(1);
}

// Remove diretório temporário
removeDirectory($deployDir);

echo "\n🎉 DEPLOY PREPARADO COM SUCESSO!\n";
echo "📁 Arquivo: polis-deploy.zip\n";
echo "📋 Próximos passos:\n";
echo "   1. Faça upload do ZIP para /public_html/polis/\n";
echo "   2. Extraia o ZIP no File Manager\n";
echo "   3. Configure api/conexao.php\n";
echo "   4. Acesse https://seu-dominio.com/polis\n\n";

// Funções auxiliares
function copyDirectory($src, $dst) {
    $dir = opendir($src);
    if (!is_dir($dst)) {
        mkdir($dst, 0777, true);
    }
    
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            if (is_dir($src . '/' . $file)) {
                copyDirectory($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function removeDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

function addDirectoryToZip($zip, $dir, $zipPath) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $dir . '/' . $file;
            $zipFilePath = $zipPath ? $zipPath . '/' . $file : $file;
            
            if (is_dir($filePath)) {
                $zip->addEmptyDir($zipFilePath);
                addDirectoryToZip($zip, $filePath, $zipFilePath);
            } else {
                $zip->addFile($filePath, $zipFilePath);
            }
        }
    }
}
?>