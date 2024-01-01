<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use DateTime;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskClass(): void
    {
        $task = new Task();
        $task->setContent("le content de ma task");
        $task->setIsDone(false);
        $task->setCreatedAt(new DateTime());
        $task->setTitle('Add a task');
        $task->setUser(null);

        $this->assertEquals('le content de ma task', $task->getContent());
        $this->assertEquals('Add a task', $task->getTitle());
        $this->assertNull($task->getUser());
        $this->assertNull($task->getId());

        $this->assertIsBool($task->isDone());
        $this->assertIsString($task->getContent());
        $this->assertIsString($task->getTitle());
        $this->assertIsObject($task->getCreatedAt());

        $this->assertFalse($task->isDone());
        $task->toggle();
        $this->assertTrue($task->isDone());
    }
}
