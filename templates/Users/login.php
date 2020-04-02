<div class="users form">
    <?= $this->Flash->render() ?>
    <?= $this->Form->create() ?>
<!--    <fieldset>-->
        <legend><?= __('Inloggen') ?></legend>
        <?= $this->Form->control('username', ['label' => 'gebruikersnaam', 'name' => 'username']) ?>
        <?= $this->Form->control('password', ['label' => 'wachtwoord', 'name' => 'password']); ?>
<!--    </fieldset>-->
    <?= $this->Form->button(__('Login')); ?>
    <?= $this->Form->end() ?>
</div>