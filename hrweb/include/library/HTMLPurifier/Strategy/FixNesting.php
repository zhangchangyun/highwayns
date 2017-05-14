﻿<?php


class HTMLPurifier_Strategy_FixNesting extends HTMLPurifier_Strategy
{

    public function execute($tokens, $config, $context)
    {

        //####################################################################//
        // Pre-processing

        // O(n) pass to convert to a tree, so that we can efficiently
        // refer to substrings
        $top_node = HTMLPurifier_Arborize::arborize($tokens, $config, $context);

        // get a copy of the HTML definition
        $definition = $config->getHTMLDefinition();

        $excludes_enabled = !$config->get('Core.DisableExcludes');

        // setup the context variable 'IsInline', for chameleon processing
        // is 'false' when we are not inline, 'true' when it must always
        // be inline, and an integer when it is inline for a certain
        // branch of the document tree
        $is_inline = $definition->info_parent_def->descendants_are_inline;
        $context->register('IsInline', $is_inline);

        // setup error collector
        $e =& $context->get('ErrorCollector', true);

        //####################################################################//
        // Loop initialization

        // stack that contains all elements that are excluded
        // it is organized by parent elements, similar to $stack,
        // but it is only populated when an element with exclusions is
        // processed, i.e. there won't be empty exclusions.
        $exclude_stack = array($definition->info_parent_def->excludes);

        // variable that contains the start token while we are processing
        // nodes. This enables error reporting to do its job
        $node = $top_node;
        // dummy token
        list($token, $d) = $node->toTokenPair();
        $context->register('CurrentNode', $node);
        $context->register('CurrentToken', $token);

        //####################################################################//
        // Loop

        // We need to implement a post-order traversal iteratively, to
        // avoid running into stack space limits.  This is pretty tricky
        // to reason about, so we just manually stack-ify the recursive
        // variant:
        //
        //  function f($node) {
        //      foreach ($node->children as $child) {
        //          f($child);
        //      }
        //      validate($node);
        //  }
        //
        // Thus, we will represent a stack frame as array($node,
        // $is_inline, stack of children)
        // e.g. array_reverse($node->children) - already processed
        // children.

        $parent_def = $definition->info_parent_def;
        $stack = array(
            array($top_node,
                  $parent_def->descendants_are_inline,
                  $parent_def->excludes, // exclusions
                  0)
            );

        while (!empty($stack)) {
            list($node, $is_inline, $excludes, $ix) = array_pop($stack);
            // recursive call
            $go = false;
            $def = empty($stack) ? $definition->info_parent_def : $definition->info[$node->name];
            while (isset($node->children[$ix])) {
                $child = $node->children[$ix++];
                if ($child instanceof HTMLPurifier_Node_Element) {
                    $go = true;
                    $stack[] = array($node, $is_inline, $excludes, $ix);
                    $stack[] = array($child,
                        // ToDo: I don't think it matters if it's def or
                        // child_def, but double check this...
                        $is_inline || $def->descendants_are_inline,
                        empty($def->excludes) ? $excludes
                                              : array_merge($excludes, $def->excludes),
                        0);
                    break;
                }
            };
            if ($go) continue;
            list($token, $d) = $node->toTokenPair();
            // base case
            if ($excludes_enabled && isset($excludes[$node->name])) {
                $node->dead = true;
                if ($e) $e->send(E_ERROR, 'Strategy_FixNesting: Node excluded');
            } else {
                // XXX I suppose it would be slightly more efficient to
                // avoid the allocation here and have children
                // strategies handle it
                $children = array();
                foreach ($node->children as $child) {
                    if (!$child->dead) $children[] = $child;
                }
                $result = $def->child->validateChildren($children, $config, $context);
                if ($result === true) {
                    // nop
                    $node->children = $children;
                } elseif ($result === false) {
                    $node->dead = true;
                    if ($e) $e->send(E_ERROR, 'Strategy_FixNesting: Node removed');
                } else {
                    $node->children = $result;
                    if ($e) {
                        // XXX This will miss mutations of internal nodes. Perhaps defer to the child validators
                        if (empty($result) && !empty($children)) {
                            $e->send(E_ERROR, 'Strategy_FixNesting: Node contents removed');
                        } else if ($result != $children) {
                            $e->send(E_WARNING, 'Strategy_FixNesting: Node reorganized');
                        }
                    }
                }
            }
        }

        //####################################################################//
        // Post-processing

        // remove context variables
        $context->destroy('IsInline');
        $context->destroy('CurrentNode');
        $context->destroy('CurrentToken');

        //####################################################################//
        // Return

        return HTMLPurifier_Arborize::flatten($node, $config, $context);
    }
}

// vim: et sw=4 sts=4
