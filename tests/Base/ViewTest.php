<?php


class ViewTest extends AimeosTestAbstract
{
	public function testCreateNoLocale()
	{
		$config = $this->createStub( '\Illuminate\Config\Repository' );
		$i18n = $this->createStub( '\Aimeos\Shop\Base\I18n' );
		$support = $this->createStub( '\Aimeos\Shop\Base\Support' );

		$context = new \Aimeos\MShop\Context();
		$context->setConfig( new \Aimeos\Base\Config\PHPArray() );
		$context->setSession( new \Aimeos\Base\Session\None() );

		$object = new \Aimeos\Shop\Base\View( $config, $i18n, $support );

		$this->assertInstanceOf( '\Aimeos\Base\View\Iface', $object->create( $context, array() ) );
	}


	public function testCreateAccessNoSuperGroup()
	{
		$config = $this->createStub( '\Illuminate\Config\Repository' );
		$config->method( 'get' )->willReturnCallback( fn( $key, $default = null ) => $default );
		$i18n = $this->createStub( '\Aimeos\Shop\Base\I18n' );
		$support = $this->createStub( '\Aimeos\Shop\Base\Support' );

		$context = new \Aimeos\MShop\Context();
		$context->setConfig( new \Aimeos\Base\Config\PHPArray() );
		$context->setSession( new \Aimeos\Base\Session\None() );
		$context->setGroups( ['1', '2'] );

		$filter = $this->createStub( '\Aimeos\Base\Criteria\Iface' );
		$filter->method( 'add' )->willReturnSelf();

		$manager = $this->createMock( '\Aimeos\MShop\Group\Manager\Standard' );
		$manager->method( 'setObject' )->willReturnSelf();
		$manager->method( 'filter' )->willReturn( $filter );
		$manager->expects( $this->once() )->method( 'search' )->willReturn( map( [
			'1' => new \Aimeos\MShop\Group\Item\Standard( 'group.', ['group.id' => '1', 'group.code' => 'super'] ),
			'2' => new \Aimeos\MShop\Group\Item\Standard( 'group.', ['group.id' => '2', 'group.code' => 'admin'] ),
		] ) );

		\Aimeos\MShop::cache( true ); // reset cached managers and context
		\Aimeos\MShop::inject( '\Aimeos\MShop\Group\Manager\Standard', $manager );

		$object = new \Aimeos\Shop\Base\View( $config, $i18n, $support );
		$view = $object->create( $context, array() );

		$this->assertTrue( $view->access( 'admin' ) );
		$this->assertFalse( $view->access( 'super' ) );

		\Aimeos\MShop::inject( '\Aimeos\MShop\Group\Manager\Standard', null );
	}
}
