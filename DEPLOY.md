# 🚀 Deploy Guide - Hostinger Git Deploy

## Configuração do Git Deploy na Hostinger

### 1. Acessar o hPanel da Hostinger
1. Faça login no [hPanel da Hostinger](https://hpanel.hostinger.com)
2. Selecione seu site/domínio
3. Procure por **"Git Deploy"** ou **"Advanced" → "Git Deploy"**

### 2. Criar Novo Git Deploy
1. Clique em **"Create new Git deploy"**
2. Configure os seguintes dados:
   - **Repository URL**: `https://github.com/salgadocpv/polis.git`
   - **Branch**: `master`
   - **Deploy path**: `/public_html/polis`
   - **Webhook**: ✅ Habilitar (deploy automático)

### 3. Configurar Arquivo de Conexão no Servidor
⚠️ **IMPORTANTE**: Após o primeiro deploy, você precisa configurar a conexão do banco:

1. Acesse **File Manager** no hPanel
2. Navegue até `/public_html/polis/api/`
3. Copie o arquivo `conexao.exemplo.php` para `conexao.php`
4. Edite `conexao.php` e atualize a senha do banco:
   ```php
   define('DB_PASS', 'SUA_SENHA_REAL_AQUI');
   ```

### 4. Testar o Deploy
```bash
# No seu ambiente local, faça um commit de teste:
git add .
git commit -m "Teste de deploy automático 🚀"
git push origin master
```

### 5. Verificar Deploy
- Acesse seu site: `https://seu-dominio.com/polis`
- Verifique logs no hPanel: **Git Deploy → View Logs**
- Em caso de erro, check **Error Logs** no hPanel

## 🔧 Comandos para Deploy Local → Produção

Agora você pode usar estes comandos via Claude Code:

```bash
# Fazer commit das mudanças
git add .
git commit -m "Descrição das alterações"

# Deploy automático para produção
git push origin master
```

## 📁 Arquivos Ignorados (.gitignore)
- ✅ Fotos de colaboradores não são enviadas
- ✅ Logs e arquivos temporários ignorados
- ✅ Configurações locais protegidas

## 🆘 Troubleshooting

### Git Deploy Não Funciona
**Passos para resolver:**

1. **Verificar Configuração Git Deploy:**
   - hPanel → Git Deploy
   - Repository URL: `https://github.com/Salgadocpv/polis.git` (com 'S' maiúsculo)
   - Branch: `master`
   - Deploy path: `/public_html/polis`
   - Webhook: ✅ ATIVO

2. **Verificar Logs:**
   - Git Deploy → **View Logs**
   - Procure por erros de:
     - Autenticação
     - Permissões
     - Path incorreto

3. **Teste Manual:**
   - Clique em **"Deploy Now"** para forçar deploy
   - Aguarde 2-5 minutos

4. **Repositório Público:**
   - Confirme se o repositório GitHub está **PÚBLICO**
   - Hostinger não acessa repositórios privados sem token

### Soluções Alternativas

#### Opção 1: Deploy Manual via File Manager
1. **Download do ZIP:**
   - GitHub → Code → Download ZIP
   - Extrair localmente

2. **Upload via File Manager:**
   - hPanel → File Manager
   - Navegar para `/public_html/polis/`
   - Upload todos os arquivos
   - Configurar `api/conexao.php`

#### Opção 2: Deploy via FTP
```bash
# Use cliente FTP (FileZilla, WinSCP, etc.)
# Host: ftp.hostinger.com
# Usuário: seu_usuario_ftp
# Senha: sua_senha_ftp
# Diretório: /public_html/polis/
```

### Erro de Permissões
Se houver erro de permissões, acesse **File Manager** e configure:
- Pastas: `755`
- Arquivos PHP: `644`
- Pasta `img/colaboradores/`: `755`

### Erro de Banco
- Verifique se `api/conexao.php` tem a senha correta
- Confirme se o banco tem as tabelas criadas
- Check **Error Logs** no hPanel para detalhes

### Verificar se Deploy Funcionou
1. **Acesse**: `https://seu-dominio.com/polis`
2. **File Manager**: Verifique se arquivos estão em `/public_html/polis/`
3. **Error Logs**: Check se há erros PHP