<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    require_once 'PeopleRepository.php';
    require_once 'People.php';

    $peopleRepository = new PeopleRepository();
    // Fill the table with test data
    for ($i = 0; $i < 10; $i++) {
        $peopleRepository->save(
            "Name $i",
            "LastName $i",
            date('Y-m-d', strtotime( '-' . mt_rand(10, 30) . ' years')),
            rand(0, 1),
            "City $i"
        );
    }

    // Get people ids which less than 5
    $people = new People('id', '5', '<');
    // Delete these people
    $people->deletePeople();
} catch (Exception $e) {
    echo $e->getMessage();
}
