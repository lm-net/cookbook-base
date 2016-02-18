<?php

namespace App\Model;

use Nette;

/**
 * Abstraktní třída, která reprezentuje tabulku v databázi
 */
abstract class CookbookTable extends Nette\Object {

    /** @var Nette\Database\Context connection do databáze */
    protected $database;

    /** @var String název databázové tabulky */
    protected static $table_name;

    /** @var String název primárního klíče */
    protected static $pk_column;

    /** @var String sloupec podle kterého se řadí záznamy na výstupu */
    protected static $order_by;

    /**
     * Nastaví connection do databáze.
     * 
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    /**
     * Vrátí všechny záznamy v tabulce
     * 
     * @return Nette\Database\Table\Selection
     */
    public function listTable() {
        return $this->database->table(static::$table_name)->order(static::$order_by);
    }

    /**
     * Vrátí konkrétní záznam z tabulky na základě primárního klíče
     * 
     * @return Nette\Database\Table\Selection
     */
    public function getById($id) {
        return $this->database->table(static::$table_name)->get($id);
    }

    /**
     * Vrátí konkrétní záznam z tabulky na základě sloupce name (název)
     * 
     * @return Nette\Database\Table\Selection
     */
    public function getByName($name) {
        return $this->database->table(static::$table_name)->where('name = ?', $name);
    }

    /**
     * Vloží do databáze nový záznam
     * 
     * @return Nette\Database\Table\IRow|int|bool
     */
    public function insert($data) {
        return $this->database->table(static::$table_name)->insert($data);
    }

}
