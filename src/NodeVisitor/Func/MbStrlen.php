<?php

namespace Inn20\PhpVirus\NodeVisitor\Func;

use PhpParser\Node;

class MbStrlen
{

    function __invoke(Node\Expr\FuncCall $node)
    {
        $strlen = new Strlen();
        return $strlen($node);
    }

}