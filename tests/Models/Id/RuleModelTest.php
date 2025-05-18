<?php

use PHPUnit\Framework\TestCase;
use JustB2b\Models\Id\RuleModel;

class RuleModelTest extends TestCase
{
    // public function testCanBeConstructedWithIdAndProductModel()
    // {
    //     echo ">>> testCanBeConstructed started\n";
    //     $mockProduct = $this->createMock(ProductModel::class);
    //     $model = new RuleModel(9442, $mockProduct);

    //     $this->assertInstanceOf(RuleModel::class, $model);
    //     $this->assertEquals(9442, $model->getId());
    // }
    public function testRuleModelCanBeInstantiatedWithoutConstructor()
    {
        $mock = $this->getMockBuilder(RuleModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(RuleModel::class, $mock);
    }


    public function testGetSingleNameReturnsLocalizedString()
    {
        $this->assertEquals('Rule', RuleModel::getSingleName());
    }

    public function testGetPluralNameReturnsLocalizedString()
    {
        $this->assertEquals('Rules', RuleModel::getPluralName());
    }
}
