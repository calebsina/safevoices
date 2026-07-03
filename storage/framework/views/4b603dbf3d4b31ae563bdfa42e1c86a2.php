<?php if($deprecated !== false): ?>
<?php ($text = $deprecated === true ? 'deprecated' : "deprecated:$deprecated"); ?>
<?php $__env->startComponent('scribe::components.badges.base', ['colour' => 'darkgoldenrod', 'text' => $text]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php /**PATH D:\caleb\api\safevoice-api\vendor\knuckleswtf\scribe\resources\views\components\badges\deprecated.blade.php ENDPATH**/ ?>