<?php

class Database {
    public static function connect() {
        return new PDO('mysql:host=localhost;dbname=todo_db', 'root', 'root');
    }
}
