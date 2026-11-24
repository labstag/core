<?php

namespace Labstag\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class JsonContains extends FunctionNode
{
    public $haystack = null;
    public $needle = null;

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->haystack = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->needle = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'JSON_CONTAINS(' . $this->haystack->dispatch($sqlWalker) . ', ' . $this->needle->dispatch($sqlWalker) . ')';
    }
}
