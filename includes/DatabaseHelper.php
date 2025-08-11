<?php

class DatabaseHelper {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }

    /**
     * Executa query paginada
     */
    public function getPaginatedResults($baseQuery, $params = [], $page = 1, $perPage = 10, $searchQuery = '', $searchFields = []) {
        $offset = ($page - 1) * $perPage;
        
        // Construir WHERE para busca
        $whereClause = '';
        $searchParams = [];
        
        if (!empty($searchQuery) && !empty($searchFields)) {
            $searchConditions = [];
            foreach ($searchFields as $field) {
                $searchConditions[] = "$field LIKE ?";
                $searchParams[] = "%$searchQuery%";
            }
            $whereClause = ' AND (' . implode(' OR ', $searchConditions) . ')';
        }
        
        // Query para contar total
        $countQuery = "SELECT COUNT(*) as total FROM ($baseQuery $whereClause) as temp_table";
        $countStmt = $this->conn->prepare($countQuery);
        
        $allParams = array_merge($params, $searchParams);
        if (!empty($allParams)) {
            $types = str_repeat('s', count($allParams));
            $countStmt->bind_param($types, ...$allParams);
        }
        
        $countStmt->execute();
        $totalResult = $countStmt->get_result();
        $total = $totalResult->fetch_assoc()['total'];
        $countStmt->close();
        
        // Query para dados paginados
        $dataQuery = $baseQuery . $whereClause . " LIMIT ? OFFSET ?";
        $dataStmt = $this->conn->prepare($dataQuery);
        
        $finalParams = array_merge($allParams, [$perPage, $offset]);
        $types = str_repeat('s', count($allParams)) . 'ii';
        $dataStmt->bind_param($types, ...$finalParams);
        
        $dataStmt->execute();
        $result = $dataStmt->get_result();
        
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        $dataStmt->close();
        
        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => $page < ceil($total / $perPage),
                'has_prev' => $page > 1
            ]
        ];
    }

    /**
     * Busca global em múltiplas tabelas
     */
    public function globalSearch($searchTerm, $page = 1, $perPage = 20) {
        $searchTerm = "%$searchTerm%";
        
        $queries = [
            'clientes' => "
                SELECT 'cliente' as type, id, nome as title, email as subtitle, 
                       CONCAT('Cliente - ', cidade) as description, 'fas fa-user' as icon
                FROM clientes 
                WHERE nome LIKE ? OR email LIKE ? OR cpf_cnpj LIKE ? OR cidade LIKE ?
            ",
            'colaboradores' => "
                SELECT 'colaborador' as type, id, nome as title, email as subtitle, 
                       CONCAT(cargo, ' - ', departamento) as description, 'fas fa-users' as icon
                FROM colaboradores 
                WHERE nome LIKE ? OR email LIKE ? OR cpf LIKE ? OR cargo LIKE ?
            ",
            'projetos' => "
                SELECT 'projeto' as type, p.id, p.nome as title, c.nome as subtitle,
                       CONCAT('Status: ', IFNULL(p.status, 'N/A'), ' - ', IFNULL(p.responsavel, 'N/A')) as description, 
                       'fas fa-project-diagram' as icon
                FROM projetos p
                LEFT JOIN clientes c ON p.cliente_id = c.id
                WHERE p.nome LIKE ? OR p.responsavel LIKE ? OR p.status LIKE ? OR p.descricao LIKE ?
            "
        ];
        
        $unionQuery = implode(' UNION ALL ', $queries);
        $finalQuery = "SELECT * FROM ($unionQuery) as results ORDER BY title";
        
        // Preparar parâmetros (4 parâmetros por query)
        $params = array_fill(0, 12, $searchTerm);
        
        return $this->getPaginatedResults($finalQuery, $params, $page, $perPage);
    }
}

?>