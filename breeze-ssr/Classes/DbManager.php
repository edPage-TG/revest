<?php

namespace API\SSR\Classes;

use API\SSR\Utils\DbInfo;

abstract class DbManager
{
    protected $database;
    protected $table;

    public function __construct()
    {
        $this->connectToDb();
    }

    private function connectToDb()
    {
        try {
            $this->database = new \PDO(
                'mysql:host=' . DbInfo::getHost() . ';dbname=' . DbInfo::getDbName() . ';charset=' . DbInfo::CHARSET,
                DbInfo::getUsername(),
                DbInfo::getPassword(),
                array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
            );
        } catch (\Exception $e) {
        }
    }

    public function getItems($tbl = null)
    {
        $table = is_null($tbl) ? $this->table : $tbl;

        return $this->database->query("SELECT * FROM $table");
    }

    public function getItemsByParent($parent, $tbl = null)
    {
        $table = is_null($tbl) ? $this->table : $tbl;

        $req = $this->database->prepare("SELECT * FROM $table WHERE parent = ?");
        $req->execute(array($parent));

        return $req;
    }

    public function getItemById($id, $tbl = null)
    {
        $table = is_null($tbl) ? $this->table : $tbl;

        $req = $this->database->prepare("SELECT * FROM $table WHERE id = ?");
        $req->execute(array($id));

        return $req;
    }

    public function getItemBySlug($slug, $tbl = null)
    {
        $table = is_null($tbl) ? $this->table : $tbl;

        $req = $this->database->prepare("SELECT * FROM $table WHERE slug = ?");
        $req->execute(array($slug));

        return $req;
    }

    public function insertItem($newItem, $tbl = null)
    {
        $table = is_null($tbl) ? $this->table : $tbl;

        try {

            foreach ($newItem as $field => $value) {
                $vals[] = ':' . $field;
            }

            $values = implode(',', $vals);
            $fields = implode(',', array_keys($newItem));

            $query = "INSERT INTO $table ($fields) VALUES ($values)";

            $rows = $this->database->prepare($query);

            foreach ($newItem as $field => $value) {
                $rows->bindValue(':' . $field, $value);
            }

            $rows->execute();

            return $this->database->lastInsertId();
        } catch (\Exception $e) {
            die('Erreur' . $e->getMessage());
        }

        return 0;
    }

    public function updateItemData($column, $newValue, $id, $tbl = null)
    {
        $table = is_null($tbl) ? $this->table : $tbl;

        $req = $this->database->prepare("UPDATE $table SET $column = ? WHERE id = ?");
        $res = $req->execute(array($newValue, $id));

        return $res;
    }

    public function deleteItem($id, $tbl = null)
    {
        $table = is_null($tbl) ? $this->table : $tbl;

        $req = $this->database->prepare("DELETE FROM $table WHERE id = ?");
        $res = $req->execute(array($id));

        return $res;
    }
}
