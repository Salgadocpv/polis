<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'estados':
        getEstados();
        break;
    case 'cidades':
        getCidades($_GET['estado'] ?? '');
        break;
    case 'bairros':
        getBairros($_GET['cidade'] ?? '');
        break;
    case 'ruas':
        getRuas($_GET['bairro'] ?? '');
        break;
    case 'cep':
        getAddressByCEP($_GET['cep'] ?? '');
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação não especificada']);
        break;
}

function getEstados() {
    // Buscar estados da API do IBGE
    $url = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome';
    
    $response = @file_get_contents($url);
    
    if ($response === false) {
        // Fallback para lista estática em caso de erro na API
        $estados = [
            ['sigla' => 'AC', 'nome' => 'Acre'],
            ['sigla' => 'AL', 'nome' => 'Alagoas'],
            ['sigla' => 'AP', 'nome' => 'Amapá'],
            ['sigla' => 'AM', 'nome' => 'Amazonas'],
            ['sigla' => 'BA', 'nome' => 'Bahia'],
            ['sigla' => 'CE', 'nome' => 'Ceará'],
            ['sigla' => 'DF', 'nome' => 'Distrito Federal'],
            ['sigla' => 'ES', 'nome' => 'Espírito Santo'],
            ['sigla' => 'GO', 'nome' => 'Goiás'],
            ['sigla' => 'MA', 'nome' => 'Maranhão'],
            ['sigla' => 'MT', 'nome' => 'Mato Grosso'],
            ['sigla' => 'MS', 'nome' => 'Mato Grosso do Sul'],
            ['sigla' => 'MG', 'nome' => 'Minas Gerais'],
            ['sigla' => 'PA', 'nome' => 'Pará'],
            ['sigla' => 'PB', 'nome' => 'Paraíba'],
            ['sigla' => 'PR', 'nome' => 'Paraná'],
            ['sigla' => 'PE', 'nome' => 'Pernambuco'],
            ['sigla' => 'PI', 'nome' => 'Piauí'],
            ['sigla' => 'RJ', 'nome' => 'Rio de Janeiro'],
            ['sigla' => 'RN', 'nome' => 'Rio Grande do Norte'],
            ['sigla' => 'RS', 'nome' => 'Rio Grande do Sul'],
            ['sigla' => 'RO', 'nome' => 'Rondônia'],
            ['sigla' => 'RR', 'nome' => 'Roraima'],
            ['sigla' => 'SC', 'nome' => 'Santa Catarina'],
            ['sigla' => 'SP', 'nome' => 'São Paulo'],
            ['sigla' => 'SE', 'nome' => 'Sergipe'],
            ['sigla' => 'TO', 'nome' => 'Tocantins']
        ];
        echo json_encode($estados);
        return;
    }
    
    $data = json_decode($response, true);
    
    if ($data) {
        $estados = array_map(function($estado) {
            return [
                'sigla' => $estado['sigla'],
                'nome' => $estado['nome']
            ];
        }, $data);
        
        echo json_encode($estados);
    } else {
        echo json_encode(['error' => 'Erro ao processar dados dos estados']);
    }
}

function getCidades($estado) {
    if (empty($estado)) {
        echo json_encode(['error' => 'Estado não informado']);
        return;
    }
    
    // Buscar cidades da API do IBGE
    $url = "https://servicodados.ibge.gov.br/api/v1/localidades/estados/{$estado}/municipios?orderBy=nome";
    
    $response = @file_get_contents($url);
    
    if ($response === false) {
        // Fallback para algumas cidades principais em caso de erro na API
        $cidadesFallback = [
            'SP' => ['São Paulo', 'Campinas', 'Santos', 'Ribeirão Preto', 'Sorocaba', 'São José dos Campos'],
            'RJ' => ['Rio de Janeiro', 'Niterói', 'Duque de Caxias', 'Nova Iguaçu', 'São Gonçalo'],
            'MG' => ['Belo Horizonte', 'Uberlândia', 'Contagem', 'Juiz de Fora', 'Betim'],
            'PR' => ['Curitiba', 'Londrina', 'Maringá', 'Ponta Grossa', 'Cascavel'],
            'RS' => ['Porto Alegre', 'Caxias do Sul', 'Pelotas', 'Canoas', 'Santa Maria'],
            'BA' => ['Salvador', 'Feira de Santana', 'Vitória da Conquista', 'Camaçari'],
            'SC' => ['Florianópolis', 'Joinville', 'Blumenau', 'São José'],
            'GO' => ['Goiânia', 'Aparecida de Goiânia', 'Anápolis', 'Rio Verde'],
            'PE' => ['Recife', 'Jaboatão dos Guararapes', 'Olinda', 'Caruaru'],
            'CE' => ['Fortaleza', 'Caucaia', 'Juazeiro do Norte', 'Maracanaú']
        ];
        
        $cidades = $cidadesFallback[$estado] ?? ['Cidade não disponível'];
        echo json_encode($cidades);
        return;
    }
    
    $data = json_decode($response, true);
    
    if ($data && is_array($data)) {
        $cidades = array_map(function($cidade) {
            return $cidade['nome'];
        }, $data);
        
        echo json_encode($cidades);
    } else {
        echo json_encode(['error' => 'Erro ao processar dados das cidades']);
    }
}

function getBairros($cidade) {
    if (empty($cidade)) {
        echo json_encode(['error' => 'Cidade não informada']);
        return;
    }
    
    // Base de dados simplificada de bairros por cidade (exemplos genéricos)
    $bairros = [
        'Centro', 'Vila Nova', 'Jardim das Flores', 'Bela Vista', 'Santo Antônio',
        'São José', 'Santa Rita', 'Boa Esperança', 'Industrial', 'Comercial',
        'Residencial', 'Parque das Árvores', 'Alto da Colina', 'Vila Operária',
        'Jardim América', 'Novo Horizonte', 'Vila Progresso', 'São Francisco',
        'Santa Maria', 'Jardim Europa', 'Vila Brasil', 'Centro Histórico',
        'Zona Norte', 'Zona Sul', 'Distrito Industrial'
    ];
    
    echo json_encode($bairros);
}

function getRuas($bairro) {
    if (empty($bairro)) {
        echo json_encode(['error' => 'Bairro não informado']);
        return;
    }
    
    // Base de dados simplificada de ruas por bairro (exemplos genéricos)
    $ruas = [
        'Rua das Flores', 'Avenida Principal', 'Rua São José', 'Rua Santa Maria',
        'Avenida Brasil', 'Rua do Comércio', 'Rua 7 de Setembro', 'Rua Tiradentes',
        'Avenida Getúlio Vargas', 'Rua Dom Pedro II', 'Rua da Paz', 'Rua da Independência',
        'Avenida Santos Dumont', 'Rua Barão do Rio Branco', 'Rua Marechal Deodoro',
        'Rua José Bonifácio', 'Avenida Presidente Vargas', 'Rua Rio Branco',
        'Rua 15 de Novembro', 'Avenida Paulista', 'Rua da Liberdade', 'Rua do Progresso'
    ];
    
    echo json_encode($ruas);
}

function getAddressByCEP($cep) {
    if (empty($cep)) {
        echo json_encode(['error' => 'CEP não informado']);
        return;
    }
    
    // Remove caracteres não numéricos do CEP
    $cep = preg_replace('/[^0-9]/', '', $cep);
    
    if (strlen($cep) != 8) {
        echo json_encode(['error' => 'CEP inválido']);
        return;
    }
    
    // Usar API real do ViaCEP
    $url = "https://viacep.com.br/ws/{$cep}/json/";
    
    // Configurar contexto para requisição HTTP
    $context = stream_context_create([
        'http' => [
            'timeout' => 10, // Timeout de 10 segundos
            'user_agent' => 'Mozilla/5.0 (compatible; Polis-System/1.0)'
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo json_encode(['error' => 'Erro ao consultar CEP. Verifique sua conexão.']);
        return;
    }
    
    $data = json_decode($response, true);
    
    if (!$data || isset($data['erro'])) {
        echo json_encode(['error' => 'CEP não encontrado']);
        return;
    }
    
    // Verificar se tem dados básicos necessários
    if (empty($data['uf']) || empty($data['localidade'])) {
        echo json_encode(['error' => 'Dados do CEP incompletos']);
        return;
    }
    
    echo json_encode([
        'estado' => $data['uf'],
        'cidade' => $data['localidade'],
        'bairro' => $data['bairro'] ?? '',
        'rua' => $data['logradouro'] ?? '',
        'cep' => $data['cep'] ?? $cep,
        'complemento' => $data['complemento'] ?? '',
        'ddd' => $data['ddd'] ?? '',
        'gia' => $data['gia'] ?? '',
        'ibge' => $data['ibge'] ?? '',
        'siafi' => $data['siafi'] ?? ''
    ]);
}
?>