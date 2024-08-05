<?php
declare(strict_types=1);

namespace Phplama\Database;

use Exception;
use PDO;
use PDOException;
use Phplama\Database\DatabaseInterface;
use Phplama\File\File;
use Phplama\Http\Request;
use Phplama\Url\Url;

class PDOConnection implements DatabaseInterface
{
    protected static $instance;
    protected static $connection;
    
    protected static string $table     = "";
    protected static string $select    = "";
    protected static string $join      = "";
    protected static string $where     = "";
    protected static string $group_by  = "";
    protected static string $having    = "";
    protected static string $order_by  = "";
    protected static string|int $limit     = "";
    protected static string|int $offset    = "";
    protected static string $query     = "";
    protected static string $setter    = "";
    protected static array $binding           = [];
    protected static array $where_binding     = [];
    protected static array $having_binding    = [];

    public function __construct () 
    {
        
    }
    
    public static function connect (): PDO
    {
        if ( !self::$connection) 
        {
            $dbData = File::require_file('config/dbinit.php');
                        
            extract($dbData['pdo']);
            
            $dsn = "mysql:dbname={$db_name};host={$db_host}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "set NAMES {$charset} COLLATE {$collation}"
            ];
            
            try {
                self::$connection = new PDO($dsn, $db_user, $db_pass, $options);
            } catch (PDOException $e) {
                throw new Exception($e->getMessage());
            }
        }
        
        return self::$connection;
    }
    
    public static function instance (): self
    {
        self::connect();
        
        if (!self::$instance) {
            self::$instance = new PDOConnection();
        }
        
        return self::$instance;
    }
    
    public static function query (string $query = null): self
    {
        self::instance();
        
        if ( $query == null)
        {
            if ( !self::$table)
                throw new Exception('Unknown table name!');
            
            $query = 'SELECT ';
            $query .= self::$select ?: '*';
            $query .= ' FROM ' . self::$table;
            $query .= self::$join;
            $query .= self::$where;
            $query .= self::$group_by;
            $query .= self::$having;
            $query .= self::$order_by;
            $query .= self::$limit;
            $query .= self::$offset;
        }
        
        self::$query = $query;
        self::$binding = array_merge(self::$where_binding, self::$having_binding);
        
        return self::instance();
    }
    
    public static function select (): self
    {
        self::$select = implode(', ', func_get_args());
        
        return self::instance();
    }
    
    public static function table (string $table): self
    {
        self::$table = $table;
        
        return self::instance();
    }
    
    public static function join (
            string $table, 
            string $first, 
            string $second, 
            string $operator = '=', 
            string $type = 'INNER'): self 
    {
        self::$join .= " {$type} JOIN {$table} ON {$first} {$operator} {$second}";
        
        return self::instance();
    }
    
    public static function right_join (
            string $table, 
            string $first, 
            string $second, 
            string $operator = '='): self 
    {
        self::join($table, $first, $second, $operator, 'RIGHT');
        
        return self::instance();
    }
    
    public static function left_join (
            string $table, 
            string $first, 
            string $second, 
            string $operator = '='): self 
    {
        self::join($table, $first, $second, $operator, 'LEFT');
        
        return self::instance();
    }
    
    public static function where (
            string $column, 
            mixed $value, 
            string $operator = '=', 
            string $type = null) : self 
    {
        $where = '`' . $column . '` ' . $operator . ' ? ';
        if ( !self::$where) {
            $statment = " WHERE {$where}";
        } else {
            
            if ( $type == null) {
                $statment = " AND {$where}";
            } else {
                $statment = " {$type} {$where}";
            }
        }
        
        self::$where .= $statment;
        
        self::$where_binding[] = htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        
        return self::instance();        
    }
    
    public static function or_where (
            string $column, 
            mixed $value, 
            string $operator = '='): self
    {
        self::where($column, $value, $operator, 'OR');
        
        return self::instance(); 
    }
    
    public static function group_by (): self
    {
        self::$group_by = ' GROUP BY ' . implode(', ', func_get_args());
        
        return self::instance();
    }
    
    public static function having (
            string $column, 
            mixed $value, 
            string $operator = '=') : self 
    {
        $having = '`' . $column . '` ' . $operator . ' ? ';
        if ( !self::$having) {
            $statment = " HAVING {$having}";
        } else {
            $statment = " AND {$having}";
        }
        
        self::$having .= $statment;
        
        self::$having_binding[] = htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        
        return self::instance();        
    }
    
    public static function order_by(string $column, string $type = null) : self 
    {
        $sep = self::$order_by ? ', ' : ' ORDER BY ';
        
        $type = ($type != null && in_array(strtoupper($type), ['ASC', 'DESC'])) ? strtoupper($type) : 'ASC';
                
        self::$order_by .= $sep . $column . ' ' . $type;
        
        return self::instance();        
    }
    
    public static function limit(string|int $limit): self
    {
        self::$limit = " LIMIT {$limit}";
        
        return self::instance();
    }
    
    public static function offset(string|int $offset): self
    {
        self::$offset = " OFFSET {$offset}";
        
        return self::instance(); 
    }
    
    public static function fetchExecute() : mixed
    {
        self::query(self::$query);
        
        $query = trim(self::$query, ' ');
        
        $data = self::$connection->prepare($query);
        
        $data->execute(self::$binding);
        
        self::cleare();
        
        return $data;
    }
    
    public static function fetchAll(): mixed 
    {
        $data = self::fetchExecute();
        
        $result = $data->fetchAll();
        
        return $result;
    }
    
    public static function fetch(): mixed 
    {
        $data = self::fetchExecute();
        
        $result = $data->fetch();
        
        return $result;
    }
    
    public static function execute(array $data, string $query, bool $where= false): bool
    {
        self::instance();
        
        if ( !self::$table) throw new Exception ('Unknown table name!');
        
        foreach ($data as $key => $value) 
        {
            self::$setter .= "`{$key}` = ?, ";
            self::$binding[] = filter_var($value, FILTER_SANITIZE_STRING);
        }
        
        self::$setter = trim(self::$setter, ', ');
        
        $query .= self::$setter;
        $query .= $where != null ? self::$where . ' ' : '';
                
        self::$binding = $where != null ? array_merge(self::$binding, self::$where_binding) : self::$binding;
        
        $data = self::$connection->prepare($query);
                
        $result = $data->execute(self::$binding);
        
        self::cleare();
        
        return $result ? true : false;
    }
    
    public static function insert(array $data): mixed
    {
        $table = self::$table;
        $query = "INSERT INTO {$table} SET ";
        self::execute($data, $query);
        
        $object_id  = self::$connection->lastInsertId();
        $object     = self::table($table)->where('id', $object_id)->fetch();
        return $object;
    }
    
    public static function update(array $data): bool
    {
        $query = "UPDATE " . self::$table . " SET ";
        
        return self::execute($data, $query, true) ? true : false;
    }
    
    public static function delete(): bool
    {
        $query = "DELETE FROM " . self::$table;
        
        return self::execute([], $query, true) ? true : false;
    }
    
    public static function pagination (string|int $items_par_page = 15): array
    {
        self::query(self::$query);
        $query = trim(self::$query, ' ');
        
        $data = self::$connection->prepare($query);
        $data->execute();
        
        $pages = ceil($data->rowCount() / $items_par_page);
        $page = Request::get('page');
        $current_page = (!is_numeric($page) || $page < 1) ? '1' : $page;
        $offset = ($current_page - 1) * $items_par_page;
        
        self::limit($items_par_page);
        self::offset($offset);
        self::query();
        
        $data = self::fetchExecute();
        $result = $data->fetchAll();
        
        return [
            'data' => $result,
            'items_per_page' => $items_par_page,
            'pages' => $pages,
            'current_page' => $current_page
        ];
    }
    
    public static function links(string|int $current_page, string|int $pages): string
    {
        $links = '';
        
        $from = $current_page - 2;
        $to = $current_page + 2;
        
        if ($from < 2) {
            $from = 2;
            $to = $from + 4;
        }
        
        if ( $to >= $pages) {
            $diff = $to - $pages + 1;
            $from = ($from > 2) ? $from - $diff : 2;
            $to = $pages - 1;
        }
        
        if ($from < 2) $from = 1;
        if ($to >= $pages) $to = $pages - 1;
        
        if ($pages > 1) 
        {
            $links .= "<ul class='pagination'>";
            
            $full_link = Url::path(Request::fullUrl());
            $full_link = preg_replace('/\?page=(.*)/', '', $full_link);
            $full_link = preg_replace('/\&page=(.*)/', '', $full_link);
            
            $current_page_active = $current_page == 1 ? 'active' : '';
            $href = strpos($full_link, '?') ? ($full_link . '&page=1') : ($full_link . '?page=1');
            $links .= "<li class='link' $current_page_active><a href='{$href}'>First</a></li>";
            
            for($i = $from; $i <= $to; $i++) 
            {
                $current_page_active = $current_page == $i ? 'active' : '';
                $href = strpos($full_link, '?') ? ($full_link . '&page=' . $i) : ($full_link . '?page=' . $i);
                $links .= "<li class='link' $current_page_active><a href='{$href}'>{$i}</a></li>";
            }
            
            if ( $pages > 1) 
            {
                $current_page_active = $current_page == $pages ? 'active' : '';
                $href = strpos($full_link, '?') ? ($full_link . '&page=' . $pages) : ($full_link . '?page=' . $pages);
                $links .= "<li class='link' $current_page_active><a href='{$href}'>Last</a></li>";
            }
            
            $links .= "</ul>";
        }
                
        return $links;
    }

    public static function cleare() : void
    {
        self::$table     = "";
        self::$select    = "";
        self::$join      = "";
        self::$where     = "";
        self::$group_by  = "";
        self::$having    = "";
        self::$order_by  = "";
        self::$limit     = "";
        self::$offset    = "";
        self::$query     = "";
        self::$instance  = "";
        self::$setter    = "";
        self::$binding           = [];
        self::$where_binding     = [];
        self::$having_binding    = [];
    }
}