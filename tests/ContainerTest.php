<?php declare(strict_types=1);
namespace JBuncle\SimpleDic;

use PHPUnit\Framework\TestCase;
use JBuncle\SimpleDic\Stubs\AutowireClass;
use JBuncle\SimpleDic\Stubs\AutowireInterfaceClass;
use JBuncle\SimpleDic\Stubs\AutowireOptionalClass;
use JBuncle\SimpleDic\Stubs\ClassTakingInterfaceImplementations;
use JBuncle\SimpleDic\Stubs\ClassWithOptionalProperties;
use JBuncle\SimpleDic\Stubs\ClassWithOptionalUntypedProperty;
use JBuncle\SimpleDic\Stubs\ClassWithProperties;
use JBuncle\SimpleDic\Stubs\ClassWithUntypedProperty;
use JBuncle\SimpleDic\Stubs\FactoryClass;
use JBuncle\SimpleDic\Stubs\ParentClass;
use JBuncle\SimpleDic\Stubs\ParentInterface;
use JBuncle\SimpleDic\Stubs\SubClass;

/**
 * ContainerTest
 *
 * phpcs:disable JBuncle.CodeErrors.MemberInitialisation.Missing
 */
class ContainerTest extends TestCase {

    /**
     * @var Container
     */
    private $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void {
        $this->instance = new Container();
    }

    public function testGetInstanceOfClass(): void {
        $parentClass = $this->instance->getInstance(ParentClass::class);

        $this->assertInstanceOf(ParentClass::class, $parentClass);
    }

    public function testGetInstanceOfClassWithOptionParam(): void {
        $class = $this->instance->getInstance(ClassWithOptionalProperties::class);

        $this->assertInstanceOf(ClassWithOptionalProperties::class, $class);
    }

    public function testGetInstanceOfClassWithOptionalUntypedParam(): void {
        $class = $this->instance->getInstance(ClassWithOptionalUntypedProperty::class);

        $this->assertInstanceOf(ClassWithOptionalUntypedProperty::class, $class);
    }

    public function testGetInstanceOfClassWithUntypedParam(): void {
        $this->expectException(ContainerException::class);
        $class = $this->instance->getInstance(ClassWithUntypedProperty::class);
    }

    public function testAutowiring(): void {
        /** @var AutowireClass $autowiredClass */
        $autowiredClass = $this->instance->getInstance(AutowireClass::class);

        $this->assertInstanceOf(AutowireClass::class, $autowiredClass);
        $this->assertInstanceOf(ParentClass::class, $autowiredClass->getParentClass());
    }

    public function testAutowireWithOptionalArgDefined(): void {
        $this->instance->addType(ParentClass::class);
        $this->instance->addType(SubClass::class);
        /** @var AutowireOptionalClass $autowiredClass */
        $autowiredClass = $this->instance->getInstance(AutowireOptionalClass::class);

        $this->assertInstanceOf(AutowireOptionalClass::class, $autowiredClass);
        $this->assertInstanceOf(ParentClass::class, $autowiredClass->getParentClass());
        $this->assertInstanceOf(SubClass::class, $autowiredClass->getSubClass());
    }

    public function testAutowireWithOptionalArgMissing(): void {
        $this->instance->addType(ParentClass::class);
        /** @var AutowireOptionalClass $autowiredClass */
        $autowiredClass = $this->instance->getInstance(AutowireOptionalClass::class);

        $this->assertInstanceOf(AutowireOptionalClass::class, $autowiredClass);
        $this->assertInstanceOf(ParentClass::class, $autowiredClass->getParentClass());
        $this->assertNull($autowiredClass->getSubClass());
    }

    public function testAutowireInterfaceWithTypeMapping(): void {

        $this->instance->addTypeMapping(ParentInterface::class, SubClass::class);

        /** @var AutowireInterfaceClass $autowiredClass */
        $autowiredClass = $this->instance->getInstance(AutowireInterfaceClass::class);

        $this->assertInstanceOf(AutowireInterfaceClass::class, $autowiredClass);
        $this->assertInstanceOf(SubClass::class, $autowiredClass->getParentClass());
    }

    /**
     * Tests type mapping of an interface where a parent implements the same
     * interface of it's autowired members.
     *
     * Highlights bug where type mapping wasn't used on a later request for
     * an intance of a later instance (and that later instance was different).
     *
     * @return void
     */
    public function testAutoloadInterfaceWithInterfaceMembers(): void {

        $this->instance->addTypeMapping(ParentInterface::class, ClassTakingInterfaceImplementations::class);

        /** @var AutowireInterfaceClass $autowiredClass */
        $autowiredClass = $this->instance->getInstance(ParentInterface::class);

        $this->assertInstanceOf(ClassTakingInterfaceImplementations::class, $autowiredClass);
        $this->assertInstanceOf(ParentClass::class, $autowiredClass->getParentClass());

        $autowiredClass = $this->instance->getInstance(ParentInterface::class);
        $this->assertInstanceOf(ClassTakingInterfaceImplementations::class, $autowiredClass);
        $this->assertInstanceOf(ParentClass::class, $autowiredClass->getParentClass());

    }

    public function testAutowireInterfaceWithFactory(): void {

        $this->instance->addFactory(function(): SubClass {
            return new SubClass();
        });

        /** @var AutowireInterfaceClass $autowiredClass */
        $autowiredClass = $this->instance->getInstance(AutowireInterfaceClass::class);

        $this->assertInstanceOf(AutowireInterfaceClass::class, $autowiredClass);
        $this->assertInstanceOf(SubClass::class, $autowiredClass->getParentClass());
    }

    public function testAutowireInterfaceWithTypeDef(): void {

        $this->instance->addType(SubClass::class);

        /** @var AutowireInterfaceClass $autowiredClass */
        $autowiredClass = $this->instance->getInstance(AutowireInterfaceClass::class);

        $this->assertInstanceOf(AutowireInterfaceClass::class, $autowiredClass);
        $this->assertInstanceOf(SubClass::class, $autowiredClass->getParentClass());
    }

    public function testGetInstanceOfInterface(): void {
        $this->instance->addType(ParentClass::class);
        $parentClass = $this->instance->getInstance(ParentInterface::class);

        $this->assertInstanceOf(ParentInterface::class, $parentClass);
        $this->assertInstanceOf(ParentClass::class, $parentClass);
    }

    public function testGetInstanceOfInterfaceFailure(): void {
        $this->expectException(ContainerException::class);
        $parentClass = $this->instance->getInstance(ParentInterface::class);

        $this->assertInstanceOf(ParentInterface::class, $parentClass);
        $this->assertInstanceOf(ParentClass::class, $parentClass);
    }

    public function testAddTypeMapping(): void {
        $this->instance->addTypeMapping(ParentClass::class, SubClass::class);
        $class = $this->instance->getInstance(ParentClass::class);

        $this->assertInstanceOf(SubClass::class, $class);
    }

    public function testInstanceReuseForInterface(): void {
        $instance = $this->instance->getInstance(ParentClass::class);
        $secondInstance = $this->instance->getInstance(ParentInterface::class);

        $this->assertInstanceOf(ParentClass::class, $instance);
        $this->assertInstanceOf(ParentClass::class, $secondInstance);

        $this->assertEquals($instance, $secondInstance);
    }

    public function testInstanceReuseForParent(): void {
        $instance = $this->instance->getInstance(SubClass::class);
        $secondInstance = $this->instance->getInstance(ParentClass::class);

        $this->assertInstanceOf(ParentClass::class, $instance);
        $this->assertInstanceOf(ParentClass::class, $secondInstance);

        $this->assertEquals($instance, $secondInstance);
    }

    public function testAddFactoryAnonFunction(): void {

        $this->instance->addFactory(function(): ClassWithProperties {
            return new ClassWithProperties('some val');
        });

        $class = $this->instance->getInstance(ClassWithProperties::class);
        $this->assertInstanceOf(ClassWithProperties::class, $class);
    }

    public function testAddFactoryMethod(): void {
        $factory = new FactoryClass();
        $this->instance->addFactory([$factory, 'getClass']);

        $class = $this->instance->getInstance(ClassWithProperties::class);
        $this->assertInstanceOf(ClassWithProperties::class, $class);
    }

    public function testAddFactoryStaticMethod(): void {
        $this->instance->addFactory([FactoryClass::class, 'getClassStatic']);

        $class = $this->instance->getInstance(ClassWithProperties::class);
        $this->assertInstanceOf(ClassWithProperties::class, $class);
    }

}
