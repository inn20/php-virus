<?php

namespace Inn20\PhpVirus\NodeVisitor;

use Inn20\PhpVirus\Common;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class MethodCallNodeVisitor extends NodeVisitorAbstract
{

    public function leaveNode(Node $node)
    {

        if ($node instanceof Node\Expr\MethodCall && $node->getAttribute('converted') != true) {

            $node->setAttribute('parent', null);

            if (!isset($node->name->name) || !is_string($node->name->name)) {
                return null;
            }

            $newNode = Common::stringNToFuncN($node->name->name);

            if ($newNode != null) {
                $newNode->setAttribute('converted', true);
                $node->setAttribute('converted', true);
                $node->name = $newNode;
                return $node;
            }
        }
        return null;
    }
}