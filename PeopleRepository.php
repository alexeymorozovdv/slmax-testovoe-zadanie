<?php

declare(strict_types=1);

require_once 'Db.php';

class PeopleRepository
{
    /**
     * @var Db|null
     */
    private ?Db $dbConnection;

    /**
     * @param int|null $id
     * @param string|null $name
     * @param string|null $lastName
     * @param string|null $birthDate
     * @param string|null $sex
     * @param string|null $city
     * @throws Exception
     */
    public function __construct(
        private ?int $id = null,
        private ?string $name = null,
        private ?string $lastName = null,
        private ?string $birthDate = null,
        private ?string $sex = null,
        private ?string $city = null,
    ) {
        $this->dbConnection = Db::getInstance();

        if (is_numeric($id) && !$name && !$lastName && !$this->birthDate && !$this->sex && !$city) {
            // get person from db
            return $this->getById($id);
        } elseif ($id && $name && $lastName && $this->birthDate && $this->sex && $city) {
            // validate data
            $this->validate($name, $lastName, $birthDate, $sex);
            // insert person to db
            return $this->save($name, $lastName, $birthDate, $sex, $city);
        }
    }

    /**
     * Get a person by id
     *
     * @param int $id
     * @return PeopleRepository
     * @throws Exception
     */
    public function getById(int $id): PeopleRepository
    {
        $personData = $this->dbConnection->getById($id);

        return new self(
            (int) $personData['id'],
            $personData['name'],
            $personData['last_name'],
            $personData['date_of_birth'],
            $personData['sex'],
            $personData['city']
        );
    }

    /**
     * Save a person to database
     *
     * @throws Exception
     */
    public function save($name, $lastName, $birthDate, $sex, $city): bool
    {
        return $this->dbConnection->save($name, $lastName, $birthDate, $sex, $city);
    }

    /**
     * Delete a person by its id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool
    {
        return $this->dbConnection->deleteById($id);
    }

    /**
     * Convert birthDate to age
     *
     * @param string $birthDate
     * @return string
     */
    private static function convertBirthDateToAge(string $birthDate): string
    {
        return date_diff(date_create($birthDate), date_create(date("Y-m-d")))->format('%y');
    }

    /**
     * Convert 0 and 1 to 'male' and 'female'
     *
     * @param string $sex
     * @return string
     */
    private static function convertSex(string $sex): string
    {
        if ((int) $sex === 0) {
            return 'male';
        }

        if ((int) $sex === 1) {
            return 'female';
        }
    }

    /**
     * Format person's data
     *
     * @param bool $age
     * @param bool $sex
     * @return StdClass
     */
    public function formatPerson(bool $age = false, bool $sex = false): StdClass
    {
        if ($age === true) {
            $this->birthDate = self::convertBirthDateToAge($this->birthDate);
        }

        if ($sex === true) {
            $this->sex = self::convertSex($this->sex);
        }

        $result = new StdClass;
        $result->id = $this->id;
        $result->name = $this->name;
        $result->lastName = $this->lastName;
        $result->birthDate = $this->birthDate;
        $result->sex = $this->sex;
        $result->city = $this->city;

        return $result;
    }

    /**
     * Validate person's data before saving
     *
     * @param string $name
     * @param string $lastName
     * @param string $birthDate
     * @param string $sex
     * @return void
     * @throws Exception
     */
    private function validate(string$name, string $lastName, string $birthDate, string $sex): void
    {
        if (!ctype_alpha($name) || !ctype_alpha($lastName)) {
            throw new Exception('Name and last name should contain only letters.');
        }

        $dateFormat = 'Y-m-d';
        $date = DateTime::createFromFormat($dateFormat, $birthDate);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        if (!$date || $date->format($dateFormat) !== $birthDate) {
            throw new Exception("Birth date should be in $dateFormat format.");
        }

        if ((int)$sex !== 0 && (int)$sex !== 1) {
            throw new Exception('Sex can be either 0 or 1.');
        }
    }
}