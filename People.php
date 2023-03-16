<?php

declare(strict_types=1);

class People
{
    private const PEOPLE_REPOSITORY_CLASS_NAME = 'PeopleRepository';
    public array $peopleIds = [];
    private PeopleRepository $peopleRepository;
    private Db $dbConnection;
    private array $people = [];

    /**
     * @param string $firstValue
     * @param string $secondValue
     * @param string $condition
     * @throws Exception
     */
    public function __construct(string $firstValue, string $secondValue, string $condition = '')
    {
        include_once self::PEOPLE_REPOSITORY_CLASS_NAME . '.php';
        if (!class_exists(self::PEOPLE_REPOSITORY_CLASS_NAME)) {
            throw new Exception('Class not found');
        }

        $this->dbConnection = Db::getInstance();
        $this->peopleRepository = new PeopleRepository();
        if ($condition !== '' && $condition !== '>' && $condition !== '<' && $condition !== '!=') {
            throw new Exception("Condition '$condition' isn't allowed.");
        }

        $this->peopleIds = $this->getPeopleIds("`$firstValue` $condition '$secondValue'", ['id']);
    }

    /**
     * Get people ids
     *
     * @param string $condition
     * @param array $fields
     * @return array
     */
    private function getPeopleIds(string $condition, array $fields): array
    {
        $result = [];
        $peopleIds = $this->dbConnection->get($condition, $fields);

        foreach ($peopleIds as $k => $v) {
            $result[] = $v['id'];
        }

        return $result;
    }

    /**
     * Get people models by their ids
     *
     * @return array
     * @throws Exception
     */
    public function getPeople(): array
    {
        if ($this->people) {
            return $this->people;
        }

        // get people by their ids
        foreach ($this->peopleIds as $id) {
            $this->people[] = $this->peopleRepository->getById((int) $id);
        }

        return $this->people;
    }

    /**
     *  Delete people from db by their ids
     *
     * @return bool
     */
    public function deletePeople(): bool
    {
        if (!$this->peopleIds) {
            return false;
        }
        foreach ($this->peopleIds as $id) {
            $this->peopleRepository->deleteById((int)$id);
        }

        return true;
    }
}