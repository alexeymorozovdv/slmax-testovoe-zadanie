<?php

declare(strict_types=1);

class Db
{
    public const TABLE_NAME = 'people';
    public const ID_FIELD_NAME = 'id';
    public const NAME_FIELD_NAME = 'name';
    public const LAST_NAME_FIELD_NAME = 'last_name';
    public const DATE_OF_BIRTH_FIELD_NAME = 'date_of_birth';
    public const SEX_FIELD_NAME = 'sex';
    public const CITY_FIELD_NAME = 'city';
    public const TABLE_FIELDS_MAPPING = [
        self::ID_FIELD_NAME,
        self::NAME_FIELD_NAME,
        self::LAST_NAME_FIELD_NAME,
        self::DATE_OF_BIRTH_FIELD_NAME,
        self::SEX_FIELD_NAME,
        self::CITY_FIELD_NAME,
    ];

    // Hold the class instance.
    private static ?Db $instance = null;
    private PDO $connection;

    // The constructor is private to prevent initiation with outer code.
    private function __construct()
    {
        // Connect to DB
        try {
            $dbParams = require 'db_params.php';
            $this->connection = new PDO(
                'mysql:host=' . $dbParams['host'] . ';dbname=' . $dbParams['db_name'],
                $dbParams['user'],
                $dbParams['password']
            );
        } catch (PDOException $e) {
            print "Error during database connect!: " . $e->getMessage() . "<br/>";
            die();
        }
    }

    /**
     * The object is created from within the class itself only if the class has no instance.
     *
     * @return Db
     */
    public static function getInstance(): Db
    {
        if (self::$instance == null)
        {
            self::$instance = new Db();
        }

        return self::$instance;
    }

    /**
     * Save to db
     *
     * @param $name
     * @param $lastName
     * @param $birthDate
     * @param $sex
     * @param $city
     * @return bool
     */
    public function save($name, $lastName, $birthDate, $sex, $city): bool
    {
        $fields = [];
        $values = [];
        foreach (self::TABLE_FIELDS_MAPPING as $field) {
            if ($field === self::ID_FIELD_NAME) {
                continue;
            }

            $fields[] = $field;
            $values[] = ":$field";
        }

        $sql = 'INSERT INTO ' . self::TABLE_NAME;
        $sql .= '(' . implode(', ', $fields) . ') VALUES(' . implode(', ', $values) . ');';
        $statement = $this->connection->prepare($sql);

        return $statement->execute([
            ':name' => $name,
            ':last_name' => $lastName,
            ':date_of_birth' => $birthDate,
            ':sex' => $sex,
            ':city' => $city,
        ]);
    }

    /**
     * Get a person by id
     *
     * @param int $id
     * @return array
     */
    public function getById(int $id): array
    {
        $person = $this->get('`' . self::ID_FIELD_NAME . '` = ' . $id);
        if (!isset($person[0])) {
            throw new PDOException("The person with id $id wasn't found");
        }

        return $person[0];
    }

    /**
     * Get a person's fields by a condition
     *
     * @param string $condition
     * @param array $fields
     * @return array
     */
    public function get(string $condition = '', array $fields = ['*']): array
    {
        $sql = 'SELECT ' . implode(', ', $fields) . ' FROM `' . self::TABLE_NAME
            . ($condition ? ('` WHERE ' . $condition) : '') . ';';
        $result = $this->connection->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if (empty($result)) {
            throw new PDOException("A person or people with condition '$condition' weren't found");
        }

        return $result;
    }

    /**
     * Delete a person by id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        $sql = 'DELETE FROM `' . self::TABLE_NAME . '` WHERE `' . self::ID_FIELD_NAME . '` = ' . $id . ';';

        return $this->connection->prepare($sql)->execute();
    }
}