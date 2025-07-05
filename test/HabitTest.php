<?php

use PHPUnit\Framework\TestCase;
use App\Entity\Habits; 
use App\Entity\Users; 
use App\Enum\FrequenceType;

class HabitTest extends TestCase
{
    public function testSetAndGetTitle()
    {
        $habit = new Habits();
        $habit->setTitle('Lire un livre');
        $this->assertEquals('Lire un livre', $habit->getTitle());
    }

    public function testSetAndGetDescription()
    {
        $habit = new Habits();
        $habit->setDescription('Lire 10 pages chaque soir');
        $this->assertEquals('Lire 10 pages chaque soir', $habit->getDescription());
    }

    public function testSetAndGetFrequence()
    {
        $habit = new Habits();
        $habit->setFrequence(FrequenceType::QUOTIDIEN);
        $this->assertEquals(FrequenceType::QUOTIDIEN, $habit->getFrequence());
    }

    public function testSetAndGetStatut()
    {
        $habit = new Habits();
        $habit->setStatut('en cours');
        $this->assertEquals('en cours', $habit->getStatut());
    }

    public function testSetAndGetUser()
    {
        $habit = new Habits();
        $user = new Users();
        $habit->setUsersId($user);
        $this->assertSame($user, $habit->getUsersId());
    }
}
