<?php
declare(strict_types=1);

namespace Phplama\Database;


interface DatabaseInterface 
{
    public static function connect ();
    
    public static function instance (): object;
    
    public static function query (string $query = null);
    
    public static function select ();
    
    public static function table (string $table);
    
    public static function join (string $table, string $first, string $second, string $operator, string $type);
    
    public static function right_join (string $table, string $first, string $second, string $operator);
    
    public static function left_join (string $table, string $first, string $second, string $operator);
    
    public static function where (string $column, mixed $value, string $operator, string $type) ;
    
    public static function or_where ( string $column, mixed $value, string $operator);
    
    public static function group_by ();
    
    public static function having (string $column, mixed $value, string $operator);
    
    public static function order_by(string $column, string $type);
    
    public static function limit(string|int $limit);
    
    public static function offset(string|int $offset);
    
    public static function fetchExecute();
    
    public static function fetchAll();
    
    public static function fetch();
    
    public static function execute(array $data, string $query, bool $where= false);
    
    public static function insert(array $data);
    
    public static function update(array $data);
    
    public static function delete();
    
    public static function pagination (string|int $items_par_page);
    
    public static function links(string|int $current_page, string|int $pages);
    
    public static function cleare();
}