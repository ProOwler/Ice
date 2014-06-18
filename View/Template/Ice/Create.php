<?php echo '<?php'; ?>

namespace <?=$namespace?>;

use ice\core\Action;
use ice\core\Action_Context;
<?php foreach ($interfaces as $interface) {?>
use ice\core\action\<?=$interface?>;
<?php
}?>
use ice\Exception;
use ice\view\render\Php;

/**
 * Class <?=$actionName?>

 *
 * @see \ice\core\Action
 * @see \ice\core\Action_Context;
<?php foreach ($interfaces as $interface) {?>
 * @see \ice\core\action\<?=$interface?>;
<?php
}?>
 * @package <?=$namespace?>

 * @author <?=get_current_user()?>

 * @since -0
 */
class <?=$actionName?> extends Action<?php if (!empty($interfaces)) {?> implements <?=implode(', ', $interfaces)?><?php
}?>

{
    protected $viewRenderClass = Php::VIEW_RENDER_PHP_CLASS;

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $actionContext
     * @throws Exception
     * @return array
     */
    protected function run(array $input, Action_Context &$actionContext)
    {
        return ['errors' => 'Implement run() method of action class <?=$actionName?>.'];
    }
}