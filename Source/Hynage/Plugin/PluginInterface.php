<?php
namespace Hynage\Plugin;

interface PluginInterface
{
    public function getSetupForm();
    public function saveFormValues(array $values);
}
