/**
 * Funções JavaScript para interagir com o banco via db_admin.php
 * Uso interno do Claude Code
 */

class DatabaseManager {
    constructor(baseUrl = 'http://localhost/polis/db_admin.php') {
        this.baseUrl = baseUrl;
    }

    async executeSQL(sql) {
        const response = await fetch(this.baseUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'execute',
                sql: sql
            })
        });
        
        return await response.json();
    }

    async showTables() {
        const response = await fetch(this.baseUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'show_tables'
            })
        });
        
        return await response.json();
    }

    async describeTable(table) {
        const response = await fetch(this.baseUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'describe',
                table: table
            })
        });
        
        return await response.json();
    }

    async getTableCount(table) {
        const response = await fetch(this.baseUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'table_count',
                table: table
            })
        });
        
        return await response.json();
    }

    async getDatabaseInfo() {
        const response = await fetch(this.baseUrl);
        return await response.json();
    }
}

// Exemplo de uso:
// const db = new DatabaseManager();
// const result = await db.executeSQL('SELECT * FROM clientes LIMIT 5');
// console.log(result);

if (typeof module !== 'undefined' && module.exports) {
    module.exports = DatabaseManager;
}