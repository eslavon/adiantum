<?php


namespace Eslavon\Adiantum\Db;

use PDO;
use Dotenv\Dotenv;

class Db
{
    protected static $pdo = false;

    public static function getPDO()
    {
        if (self::$pdo == false) {
            Dotenv::createImmutable (__DIR__,"../../config.env")->load();
            $dsn = "mysql:host=".$_ENV["HOST_DB"].";dbname=".$_ENV["NAME_DB"].";charset=".$_ENV["CHARSET_DB"];
            $opt = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$pdo = new PDO($dsn, $_ENV["USER_DB"], $_ENV["PASSWORD_DB"], $opt);
            return self::$pdo;
        } else {
            return self::$pdo;
        }
    }

    public static function insert($table,$array)
    {
        $columnString = implode(',', array_keys($array));
        $valueString = implode(',', array_fill(0, count($array), '?'));
        $sql = "INSERT INTO ".$table." (".$columnString.") VALUES(".$valueString.")";
        $stmt = Db::getPDO()->prepare($sql);
        $stmt->execute(array_values($array));
    }

    public static function isset($table,$where,$value)
    {
        $sql = "SELECT COUNT(*) as count FROM ".$table." WHERE ".$where." = :value";
        $stmt = Db::getPDO()->prepare($sql);
        $stmt->bindParam(":value", $value);
        $stmt->execute();
        while ($row = $stmt->fetch(Db::getPDO()::FETCH_ASSOC)) {
            if ($row["count"] == 0) {
                return false;
            } else {
                return true;
            }
        }
    }

    public static function select($table,$where,$value,$column = "*")
    {
        $sql = "SELECT ".$column." FROM ".$table." WHERE ".$where." = :value";
        $stmt = Db::getPDO()->prepare($sql);
        $stmt->bindParam(":value",$value);
        $stmt->execute();
        return $stmt->fetch(Db::getPDO()::FETCH_ASSOC);
    }

    public static function update($table,$where,$value,$column,$update_value)
    {
        $sql = "UPDATE ".$table." SET ".$column." = :update_value WHERE ".$where." = :value";
        $stmt = Db::getPDO()->prepare($sql);
        $stmt->bindParam(":value",$value);
        $stmt->bindParam(":update_value",$update_value);
        $stmt->execute();
    }
}