<?php

namespace Liuggio\Fastest\Queue;

use Doctrine\Common\Collections\ArrayCollection;

// Keep the variants outside PSR-4 directories so classmaps and static analysis
// see only the installed native signature. Register lazily to preserve bootstrap behavior.
spl_autoload_register(static function (string $class): void {
    if (TestsQueueAddTrait::class !== $class) {
        return;
    }

    $addReturnType = (new \ReflectionMethod(ArrayCollection::class, 'add'))->getReturnType();

    if ($addReturnType instanceof \ReflectionNamedType && 'void' === $addReturnType->getName()) {
        require_once __DIR__ . '/../../compat/TestsQueueVoidAddTrait.php';
    } else {
        require_once __DIR__ . '/../../compat/TestsQueueLegacyAddTrait.php';
    }
}, true, true);
