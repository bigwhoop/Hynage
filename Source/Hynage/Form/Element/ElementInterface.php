<?php
namespace Hynage\Form\Element;

interface ElementInterface
{
    public function render();
    public function renderLabel();
    public function renderElement();
    public function isValid();
    public function getErrors();
}
