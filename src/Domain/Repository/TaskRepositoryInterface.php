<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Task;
use App\Domain\Entity\TaskCollection;

interface TaskRepositoryInterface
{
    public function saveTask(Task $task): Task;

    public function getTasks(?bool $areDone = null, ?\DateTime $when = null): TaskCollection;

    public function findTaskById(int $taskId): ?Task;

    public function deleteTask(Task $task): bool;
}
