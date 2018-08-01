<?php

namespace yiiunit\debug;

use yii\base\Event;
use yii\caching\Cache;
use yii\caching\FileCache;
use yii\debug\Module;
use Yii;

class ModuleTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->mockWebApplication();
    }

    // Tests :

    /**
     * Data provider for [[testCheckAccess()]]
     * @return array test data
     */
    public function dataProviderCheckAccess()
    {
        return [
            [
                [],
                '10.20.30.40',
                false
            ],
            [
                ['10.20.30.40'],
                '10.20.30.40',
                true
            ],
            [
                ['*'],
                '10.20.30.40',
                true
            ],
            [
                ['10.20.30.*'],
                '10.20.30.40',
                true
            ],
            [
                ['10.20.30.*'],
                '10.20.40.40',
                false
            ],
        ];
    }

    /**
     * @dataProvider dataProviderCheckAccess
     *
     * @param array $allowedIPs
     * @param string $userIp
     * @param bool $expectedResult
     */
    public function testCheckAccess(array $allowedIPs, $userIp, $expectedResult)
    {
        $module = new Module('debug');
        $module->allowedIPs = $allowedIPs;
        $_SERVER['REMOTE_ADDR'] = $userIp;
        $this->assertEquals($expectedResult, $this->invoke($module, 'checkAccess'));
    }

    /**
     * Test to verify toolbars html
     */
    public function testGetToolbarHtml()
    {
        $logger = $this->getMockBuilder(\yii\log\Logger::class)
            ->setMethods(['dispatch'])
            ->getMock();
        Yii::setLogger($logger);

        $module = new Module('debug');
        $module->bootstrap(Yii::$app);

        $this->assertEquals(<<<HTML
<div id="yii-debug-toolbar" data-url="/index.php?r=debug%2Fdefault%2Ftoolbar&amp;tag={$module->logTarget->tag}" style="display:none" class="yii-debug-toolbar-bottom"></div>
HTML
        ,$module->getToolbarHtml());
    }

    /**
     * Test to ensure toolbar is never cached
     */
    public function testNonCachedToolbarHtml()
    {
        $logger = $this->getMockBuilder(\yii\log\Logger::class)
            ->setMethods(['dispatch'])
            ->getMock();
        Yii::setLogger($logger);

        $module = new Module('debug');
        $module->allowedIPs = ['*'];
        Yii::$app->setModule('debug',$module);
        $module->bootstrap(Yii::$app);

        Yii::$app->set('cache', new Cache([
            'handler' => new FileCache(['cachePath' => '@yiiunit/debug/runtime/cache'])
        ]));

        $view = Yii::$app->view;
        for ($i = 0; $i <= 1; $i++) {
            ob_start();
            $module->logTarget->tag = 'tag' . $i;
            if ($view->beginCache(__FUNCTION__, ['duration' => 3])) {
                $module->renderToolbar(new Event(['sender' => $view]));
                $view->endCache();
            }
            $output[$i] = ob_get_clean();
        }
        $this->assertNotEquals($output[0], $output[1]);
    }

    /**
     * Making sure debug toolbar does not error
     * in case module ID is not "debug".
     *
     * @see https://github.com/yiisoft/yii2-debug/pull/176/
     */
    public function testToolbarWithCustomModuleID()
    {
        $logger = $this->getMockBuilder(\yii\log\Logger::class)
            ->setMethods(['dispatch'])
            ->getMock();
        Yii::setLogger($logger);

        $moduleID = 'my_debug';

        $module = new Module($moduleID);
        $module->allowedIPs = ['*'];
        Yii::$app->setModule($moduleID, $module);
        $module->bootstrap(Yii::$app);

        $view = Yii::$app->view;

        ob_start();
        $module->renderToolbar(new Event(['sender' => $view]));
        ob_end_clean();

        $this->assertTrue(true, 'should be no error');
    }

    public function testDefaultVersion()
    {
        Yii::$app->extensions['yiisoft/yii2-debug'] = [
            'name' => 'yiisoft/yii2-debug',
            'version' => '2.0.7',
        ];

        $module = new Module('debug');

        $this->assertEquals('2.0.7', $module->getVersion());
    }
} 