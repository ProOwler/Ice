<h1><?= $enjoy ?></h1>
<h2>Module <b><?= $project ?></b> was created!</h2>

<h3>
    <span>Step 1: <?= $welcome ?></span>
    <span>(create module dir)</span>
    <span>- check engine ...OK</span>
</h3>

<?php if (!$install) {?>
    <p>
        <a href="?install">Step 2: Install module <?= $project ?></a>
        <span>(create module files: generate default route, actions, views etc.)</span>
    </p>
<?php
} else {?>
    <h3>
        <span>Step 2: Installation complete</span>
        <span>(create module files)</span>
        <span>- check code generation ...OK</span>
    </h3>
    <h4>
       <a href="#" onclick="Module.check();return false;">PROFIT. Replace this page!</a>
    </h4>
<?php
}?>
