<?php
const ADD_EOF         = true;
const SCANNER_SWITCH  = true;
const SCANNER_INLINE  = true;
const SCANNER_TUNNELS = true;

class Node {
	public $next = null;
	public $up = 0;
	public $visited = 0;
}
class Action extends Node {
	public $code;
	function __construct($code) {$this->code = $code;}
}
class Terminal extends Node {
	public $name;
	function __construct($name) {$this->name = $name;}
}
class RegExp extends Terminal {
	public $regexp;
	public $code = null;
	function __construct($name, $regexp) {$this->name = $name; $this->regexp = $regexp;}
}
class NonTerminal extends Node {
	public $name;
	public $attrs;
	function __construct($name) {$this->name = $name; $this->attrs = null;}
}
class Alternative extends Node {
	public $start = null;
	public $alt = null;
	public $has_pred = 0;
	function __construct($start, $alt) {$this->start = $start; $this->alt = $alt;}
}
class Option extends Node {
	public $start = null;
	public $greedy = null;
	function __construct($start, $greedy = null) {$this->start = $start; $this->greedy = $greedy;}
}
class Iteration extends Node {
	public $min_count = null;
	public $start = null;
	public $greedy = null;
	function __construct($min_count, $start, $greedy=null) {$this->min_count = $min_count; $this->start = $start; $this->greedy = $greedy;}
}
class Epsilon extends Node {
}
class Predicate extends Node {
}
class SyntaticPredicate extends Predicate {
	public $name = null;
	public $start = null;
	public $neg = false;
	function __construct($name, $start, $neg) {$this->name = $name; $this->start = $start; $this->neg = $neg;}
}
class SemanticPredicate extends Predicate {
	public $code = null;
	function __construct($code) {$this->code = $code;}
}
class Character extends Node {
	public $ch = null;
	function __construct($ch) {$this->ch = $ch;}
}
class Charset extends Node {
	public $neg = null;
	public $set = null;
	function __construct($neg, $set) {$this->neg = $neg; $this->set = $set;}
}
class LexerDef {
	public $func;
	public $get_sym;
	public $skip; /* Set */
	public $dfa;
	function __construct($func, $get_sym, $skip = null) {
		$this->func = $func;
		$this->get_sym = $get_sym;
		$this->skip = $skip;
	}
}

const IS_USED         = 0x01;
const IS_SKIPPED      = 0x02;
const IS_RECURSIVE    = 0x04;
const IS_NULLABLE     = 0x08;
const IS_EMPTY        = 0x10;
const IS_DERIVABLE    = 0x20;
const IS_USED_IN_PRED = 0x40;
const IS_VISITED      = 0x80;

class TermDef {
	public $name;
	public $special;
	public $val;
	public $const_name;
	function __construct($name, $val, $special = false) {
		$this->name = $name;
		$this->special = $special;
		$this->val = $val;
		if ($name == '_') {
			$const_name = 'YY_UNDERSCORE';
		} else {				
			$n = strlen($name);
		    $ascii = true;
			for ($i = 0; $i < $n; $i++) {
				if (!(($name[$i] >= 'A' && $name[$i] <= 'Z') ||
				      ($name[$i] >= 'a' && $name[$i] <= 'z') ||
				      ($name[$i] >= '0' && $name[$i] <= '9') ||
		    		  ($name[$i] == '_'))) {
					$ascii = false;
					break;
				}					
			}
			if ($ascii) {
				$const_name = "YY_";
				for ($i = 0; $i < $n; $i++) {
					if (($name[$i] >= 'a' && $name[$i] <= 'z')) {
						$const_name .= chr(ord($name[$i]) - ord('a') + ord('A'));
					} else {
						$const_name .= $name[$i];
					}
				}				
			} else {
				$const_name = "YY_";
				for ($i = 0; $i < $n; $i++) {
					$const_name .= "_";
					switch ($name[$i]) {
						case "\r": $const_name .= 'CR'; break;
						case "\n": $const_name .= 'LF'; break;
						case "\t": $const_name .= 'TAB'; break;
						case "\v": $const_name .= 'VT'; break;
						case "\f": $const_name .= 'FF'; break;
						case " " : $const_name .= "SPACE"; break;
						case "!" : $const_name .= "BANG"; break;
						case '"' : $const_name .= "DOUBLE_QUOTE"; break;
						case "#" : $const_name .= "HASH"; break;
						case "$" : $const_name .= "DOLLAR"; break;
						case "%" : $const_name .= "PERCENT"; break;
						case "&" : $const_name .= "AND"; break;
						case "'" : $const_name .= "SINGLE_QUOTE"; break;
						case "(" : $const_name .= "LPAREN"; break;
						case ")" : $const_name .= "RPAREN"; break;
						case "*" : $const_name .= "STAR"; break;
						case "+" : $const_name .= "PLUS"; break;
						case "," : $const_name .= "COMMA"; break;
						case "-" : $const_name .= "MINUS"; break;
						case "." : $const_name .= "POINT"; break;
						case "/" : $const_name .= "SLASH"; break;
						case ":" : $const_name .= "COLON"; break;
						case ";" : $const_name .= "SEMICOLON"; break;
						case "<" : $const_name .= "LESS"; break;
						case "=" : $const_name .= "EQUAL"; break;
						case ">" : $const_name .= "GREATER"; break;
						case "?" : $const_name .= "QUERY"; break;
						case "@" : $const_name .= "AT"; break;
						case "[" : $const_name .= "LBRACK"; break;
						case "\\": $const_name .= "BACK_SLASH"; break;
						case "]" : $const_name .= "RBRACK"; break;
						case "^" : $const_name .= "UPARROW"; break;
						case "_" : $const_name .= "UNDERSCORE"; break;
						case "`" : $const_name .= "ACCENT"; break;
						case "{" : $const_name .= "LBRACE"; break;
						case "|" : $const_name .= "BAR"; break;
						case "}" : $const_name .= "RBRACE"; break;
						case "~" : $const_name .= "TILDE"; break;
						default:
							if (($name[$i] >= 'A' && $name[$i] >= 'Z') ||
							    ($name[$i] >= '0' && $name[$i] >= '9')) {
								$const_name .= $name[$i];
							} else if ($name[$i] >= 'a' && $name[$i] <= 'z') {
								$const_name .= chr(ord($name[$i]) - ord('a') + ord('A'));
							} else  {
								$const_name .=
									chr(ord('0') + (($c >> 6) % 8)) .
									chr(ord('0') + (($c >> 3) % 8)) .
									chr(ord('0') + ($c % 8));									
							}
					}
				}
			}				
		}
		$this->const_name = $const_name;
	}
}
class NonTermDef {
	public $name;
	public $attrs;
	public $code; /* String */
	public $ast;  /* Node   */
	public $lexer     = null;
	public $use_lexer = null;
//	public $used      = 0;
	public $flags     = 0;
	public $first     = null;     /* Set */
	public $follow    = array();  /* Set */
	public $occurance = array();  /* Set */
	public $la_nfa    = null;     /* state number */
	function __construct($name, $attrs, $code, $ast) {
		$this->name = $name;
		$this->attrs = $attrs;
		$this->code = $code;
		$this->ast = $ast;
	}
}
class FA {
	public $n        = 0;       // number of states
	public $start    = 0;       // start state
	public $move     = array(); // transitins
	public $final    = array(); // set of final states
	public $ungreedy = array(); // set of ungreedy states
}
class Grammar {
	public $start = null;
	public $sub_start = array();
	public $case_sensetive = true;
	public $global_vars = true;
	public $lineno = true;
	public $prologue = null;    // string, prologue code block
	public $epilogue = null;    // string, epilogue code block
	public $nonterm  = array(); // list of defined non-terminals (NonTermDef)
	public $pred     = array(); // list of predicates (Predicate)
	public $term     = array(); // list of used terminals (TermDef)
	public $used     = array(); // list of used non-terminals
	public $la_nfa   = null;    // ATN

	public $output = "parser.php";
	public $language = "php";
	public $indent = "\t";
	public $prefix = "yy";
}

abstract class Emitter {
	protected $f;
	protected $global_vars = true; /* declare global scanner variables */
	protected $lineno = true; /* automatic line number tracking */
	public    $indent;
	public    $cur_indent = "";
	public    $buf = "";
	public    $l_prefix;
	public    $u_prefix;

	function __construct($fn, $indent = "\t", $prefix, $global_vars, $lineno) {
		if (EMIT_TO_STDOUT) {
			$this->f = fopen("php://stdout", "w");
		} else {
			$this->f = fopen($fn, "w");
		}
		$this->indent = $indent;
		$this->global_vars = $global_vars;
		$this->lineno = $lineno;
		$this->l_prefix = strtolower($prefix);
		$this->u_prefix = strtoupper($prefix);
		$this->buf = "";
	}
	function close() {
		$this->flush();
		fclose($this->f);
	}
	function write($s) {
		$this->buf .= $s;
	}
	function flush() {
		fwrite($this->f, $this->buf);
		$this->buf = "";
	}
	function inc_indent() {
		$this->cur_indent .= $this->indent;
	}

	function dec_indent() {
		$this->cur_indent = substr($this->cur_indent, 0, -strlen($this->indent));
	}
	function indent($n=0) {
		$this->write($this->cur_indent);
		for ($i = 0; $i < $n; $i++) {
			$this->write($this->indent);
		}
	}
/* FIXME:
	abstract function scanner($func, $dfa);
	abstract function nonterm_check($grammar, $func, $nt, $scan);
	abstract function synpred($grammar, $pred, $scan);
	abstract function nonterm($grammar, $func, $nt, $scan, $first = false);
	abstract function main($func, $start_sym, $nt);
*/
}

include("llk_parser.php");
include("llk_php.php");
include("llk_c.php");
include("llk_dot.php");

function error($msg) {
	global $line;
	throw new Exception("$line: $msg\n");
}

/* AST ACTIONS */

function concat_seq(&$gl, &$gr, $gl2, $gr2) {
	$p = $gr->next;
	$gr->next = $gl2;
	while ($p !== null) {
		$q = $p->next;
		$p->next = $gl2;
//		$p->up = true;
		$p = $q;
	}
	$gr = $gr2;
}

function make_first_alt(&$gl, &$gr) {
	$gl = new Alternative($gl, null);
	$gr->up = 1;
	$gl->next = $gr;
	$gr = $gl;
}

function concat_alt($gl1, $gr1, $gl2, $gr2) {
    $gl2 = new Alternative($gl2, null);
    $p = $gl1;
    while ($p->alt !== null) $p = $p->alt;
    $p->alt = $gl2;
    $p = $gr1;
    while ($p->next !== null) $p = $p->next;
	$gr2->up = 1;
    $p->next = $gr2;
}

function make_opt(&$gl, &$gr, $greedy=null) {
	$gl = new Option($gl, $greedy);
	$gr->up = 1;
	$gl->next = $gr;
	$gr = $gl;
}

function make_iter($min_count, &$gl, &$gr, $greedy=null) {
	$p = $gr;
	$gl = $gr = new Iteration($min_count, $gl, $greedy);
	while ($p != null) {
		$q = $p->next;
		$p->next = $gl;
		$p->up = 2;
		$p = $q;
	}
}

function set_ctx($p) {
	while ($p != null) {
		if ($p instanceof Character || $p instanceof Charset) {
			$p->ctx = true;
		} else if ($p instanceof Epsilon) {
		} else if ($p instanceof Option || $p instanceof Iteration) {
			set_ctx($p->start);
		} else if ($p instanceof Alternative) {
			set_ctx($p->start);
			set_ctx($p->alt);
		} else {
			die("set_ctx(???)\n");
		}
		$p = $p->next;
	}
}

function complete_graph($gr) {
	while ($gr !== null) {
		$p = $gr->next;
		$gr->next = null;
		$gr = $p;
	}
}

function make_pred($gl, $gr, $neg = false) {
	static $n = 0;
	complete_graph($gr);
    return new SyntaticPredicate("synpred_" . ++$n, $gl, $neg);
}

/* ANALYZER */

function visit_number() {
	static $n = 0;
	return ++$n;
}

// check for undefined nonterminals
function test_undefined_nterm($grammar) {
	$ok = true;
	foreach ($grammar->used as $key => $val) {
		if (!isset($grammar->nonterm[$key])) {
			echo "Nontermonal '$key' is used but never defined\n";
			$ok = false;
		}
	}
	return $ok;
}

function traverse_nt($grammar, $nt, $visit, $flag, $with_pred) {
	$nt->flags |= $flag | IS_VISITED;
	if ($nt->ast->visited != $visit) {
		$nt->ast->visited = $visit;
		if (traverse($grammar, $nt->name, $nt->ast, $visit, $flag, $with_pred)) {
			$nt->flags |= IS_EMPTY;
		}
	}
	$nt->flags &= ~IS_VISITED;
}

function traverse($grammar, $nterm, $node, $visit, $flag, $with_pred) {
	$empty = true;
	while ($node != null) {
		if ($node instanceof Terminal ||
		    $node instanceof Character ||
		    $node instanceof Charset) {
			$empty = false;
		} else if ($node instanceof NonTerminal) {
			if (isset($grammar->nonterm[$node->name])) {
				$nt = $grammar->nonterm[$node->name];
				if (!$with_pred) {
					$nt->occurance[] = array($nterm, $node);
				}
				if ($nt->flags & IS_VISITED) {
					if (!$with_pred) {
						$nt->flags |= IS_RECURSIVE;
					}
				} else {
					traverse_nt($grammar, $nt, $visit, $flag, $with_pred);
				}
			}
			$empty = false;
		} else if ($node instanceof Alternative) {
			$q = $node;
			do {
				$empty &= traverse($grammar, $nterm, $q->start, $visit, $flag, $with_pred);
				$q = $q->alt;
			} while ($q !== null);
		} else if ($node instanceof Option) {
			$empty &= traverse($grammar, $nterm, $node->start, $visit, $flag, $with_pred);
		} else if ($node instanceof Iteration) {
			$empty &= traverse($grammar, $nterm, $node->start, $visit, $flag, $with_pred);
		} else if ($node instanceof SyntaticPredicate) {
			if ($with_pred) {
				traverse($grammar, $nterm, $node->start, $visit, $flag, true);
			}
		} else if ($node instanceof Epsilon) {
		} else { /* Action */
			$empty = false;
		}
		$node = $node->up ? null : $node->next;
	}
	return $empty;
}

function test_unused_nterm($grammar) {
	if ($grammar->start === null) {
		reset($grammar->nonterm);
		$grammar->start = key($grammar->nonterm);
	}
	traverse_nt($grammar, $grammar->nonterm[$grammar->start], visit_number(), IS_USED, false);

	$visit = visit_number();
	foreach($grammar->pred as $node) {
		if ($node instanceof SyntaticPredicate) {
			traverse($grammar, null, $node->start, $visit, IS_USED_IN_PRED, true);
		}
	}

	if (isset($grammar->nonterm['SKIP'])) {
		traverse_nt($grammar, $grammar->nonterm['SKIP'], visit_number(), IS_SKIPPED, false);
	}

	$ok = true;
	$skip = array();
	foreach ($grammar->nonterm as $name => $nt) {
		if (($nt->flags & (IS_USED | IS_USED_IN_PRED)) == 0) {
			if (($nt->flags & IS_SKIPPED) == 0) {
				echo("Nontermonal '$name' is defined but never used\n");
				$ok = false;
			} else {
				$skip[$name] = 1;
			}
		} else {
			if ($nt->flags & IS_SKIPPED) {
				echo("Nontermonal '$name' can't be skipped\n");
				$ok = false;
			}
		}
		if ($nt->flags & IS_EMPTY) {
			echo "Empty symbol: $name\n";
			$ok = false;
		}
	}

	if (count($skip) > 0) {
		$grammar->nonterm[$grammar->start]->lexer = new LexerDef("get_skip_sym", "get_sym", $skip);
	} else {
		$grammar->nonterm[$grammar->start]->lexer = new LexerDef("get_sym", "get_sym");
	}

	return $ok;
}

// Test if nonterminals are derivable to terminals
function is_derivable($grammar, $p)
{
	while ($p != null) { /* TODO: (neg) > 0 */
		if ($p instanceof NonTerminal &&
			($grammar->nonterm[$p->name]->flags & IS_DERIVABLE) == 0) {
			return false;
		} else if ($p instanceof Iteration && $p->min_count > 0 &&
		           !is_derivable($grammar, $p->start)) {
			return false;
		} else if ($p instanceof Alternative &&
		           !is_derivable($grammar, $p->start) &&
		           ($p->alt == null || !is_derivable($grammar, $p->alt))) {
			return false;
		}
        $p = $p->up ? null : $p->next;
	}
	return true;
}

function test_underivable_nterms($grammar)
{
	$derivable = 0;
	do {
    	$changed = false;
		foreach($grammar->nonterm as $name => $nt) {
			if (($nt->flags & IS_DERIVABLE) == 0) {
				if (is_derivable($grammar, $nt->ast)) {
					$nt->flags |= IS_DERIVABLE;
					$changed = true;
					$derivable++;
				}
			} else {
				$derivable++;
			}
		}
	} while ($changed);

	$ok = true;
	if (count($grammar->nonterm) != $derivable) {
		foreach($grammar->nonterm as $name => $nt) {
			if (($nt->flags & IS_DERIVABLE) == 0) {
				$ok = false;
				echo "Underivable nonterminal '$name'\n";
			}
		}
	}
	return $ok;
}

// test for nullable non-terminals
function nullable_graph($grammar, $p)
{
	$min_count = 0;
	while ($p != null) {
		if (!nullable_node($grammar, $p, $min_count)) {
			return false;
		}
		$min_count = ($p->up == 2) ? 1 : 0;
		$p = $p->next;
	}
	return true;
}

function nullable_subgraph($grammar, $p)
{
	while ($p != null) {
		if (!nullable_node($grammar, $p)) {
			return false;
		}
		$p = $p->up ? null : $p->next;
	}
	return true;
}

function nullable_node($grammar, $p, $min_count = 0)
{
	if ($p instanceof Terminal) {
		return false;
	} else if ($p instanceof NonTerminal) {
		return ($grammar->nonterm[$p->name]->flags & IS_NULLABLE) != 0;
	} else if ($p instanceof Alternative) {
		$q = $p;
		do {
			if (nullable_subgraph($grammar, $q->start)) {
				return true;
			}
			$q = $q->alt;
		} while ($q !== null);
		return false;
	} else if ($p instanceof iteration && $p->min_count > $min_count) {
		return nullable_subgraph($grammar, $p->start);
	} else { // eps, iter*, opt, sem, sync
		return true;
	}
}

function test_nullable_nterms($grammar)
{
	do {
		$changed = false;
		foreach ($grammar->nonterm as $name => $nt) {
			if (($nt->flags & IS_NULLABLE) == 0 && nullable_graph($grammar, $nt->ast)) {
				$nt->flags |= IS_NULLABLE;
				$changed = true;
			}
		}
	} while ($changed);
}

// test for circular derivations
function get_singles($grammar, &$singles, $p)
{
	while ($p !== null) {
		if ($p instanceof NonTerminal) {
    	    if ($p->up || nullable_graph($grammar, $p->next)) {
	        	$singles[$p->name] = false;
        	}
		} else if ($p instanceof Option) {
			if ($p->up || nullable_graph($grammar, $p->next)) {
				get_singles($grammar, $singles, $p->start);
			}
		} else if ($p instanceof Iteration) {
			if ($p->min_count == 0) {
				if ($p->up || nullable_graph($grammar, $p->next)) {
					get_singles($grammar, $singles, $p->start);
				}
			} else {
				// TODO: seems to work properly
				if ($p->up || nullable_graph($grammar, $p->next)) {
					get_singles($grammar, $singles, $p->start);
				}
			}
		} else if ($p instanceof Alternative) {
			if (nullable_graph($grammar, $p->next)) {
				get_singles($grammar, $singles, $p->start);
				get_singles($grammar, $singles, $p->alt);
			}
		}
		$p = (!$p->up && nullable_node($grammar, $p)) ? $p->next : null;
	}
}

function test_circular_nterms($grammar)
{
	$singles = array();
	foreach ($grammar->nonterm as $name1 => $nt1) {
		$singles[$name1] = array();
		get_singles($grammar, $singles[$name1], $nt1->ast);
	}

	do {
		$changed = false;
		foreach ($singles as $name1 => $singles1) {
			foreach ($singles1 as $name2 => $dummy) {
				$del = !isset($singles[$name2]);
				if (!$del) {
					$del = true;
					foreach ($singles as $singles2) {
						$del &= !isset($singles2[$name1]);
					}
				}
				if ($del) {
					unset($singles[$name1][$name2]);
					if (count($singles[$name1]) == 0) {
						unset($singles[$name1]);
					}
					$changed = true;
				}
			}
		}
	} while ($changed);

	$ok = true;
	if (count($singles) != 0) {
		foreach ($singles as $name1 => $singles1) {
			foreach ($singles1 as $name2 => $x) {
				$ok = false;
				echo("Circular derivation: $name1 -> $name2\n");
			}
		}
	}
	return $ok;
}

function empty_subgraph($p)
{
	while ($p !== null) {
		if ($p instanceof Terminal ||
		    $p instanceof NonTerminal ||
		    $p instanceof Character ||
		    $p instanceof Charset) {
			return false;
		} else if ($p instanceof Alternative) {
			if (!empty_subgraph($p->start) || !empty_subgraph($p->alt)) {
				return false;
			}
		} else if ($p instanceof Iteration || $p instanceof Option) {
			if (!empty_subgraph($p->start)) {
				return false;
			}
		}
		$p = $p->up ? null : $p->next;
	}
	return true;
}

function is_single_repetition($grammar, $p) {
	while ($p !== null) {
		if ($p instanceof Terminal ||
		    $p instanceof NonTerminal ||
		    $p instanceof Character ||
		    $p instanceof Charset) {
			return false;
		} else if ($p instanceof NonTerminal) {
			if (($grammar->nonterm[$p->name]->flags & IS_NULLABLE) == 0) {
				return false;
			}
		} else if ($p instanceof Alternative) {
			if (!nullable_node($grammar, $p)) {
				if ($p->up || $p->next === null || nullable_subgraph($grammar, $p->next)) {
					$q = $p;
					while ($q !== null) {
						if (is_single_repetition($grammar, $q->start)) {
							return true;
						}
						$q = $q->alt;
					}
				}
				return false;
			}
		} else if ($p instanceof Iteration && $p->min_count > 0) {
			return ($p->up || $p->next === null || nullable_subgraph($grammar, $p->next));
		} else { /* Option, Epsilon, Action, Predicate? */
		}
		$p = $p->up ? null : $p->next;
	}
	return false;
}

function ambiguity($grammar, $nt, $p, $alts) {
	$ok = true;
	while ($p !== null) {
		if ($p instanceof Alternative) {
			if ($alts) {
				$n = 0;
				$q = $p;
				while ($q !== null) {
					if (empty_subgraph($q->start)) {
						$n++;
					}
					$q = ($q instanceof Alternative) ? $q->alt : null;
				}
				if ($n > 1) {
					echo "Ambiguous grammar in rule '$nt': at most one alternative must be empty\n";
					$ok = false;
				} else {
					$n = 0;
					$q = $p;
					while ($q !== null) {
						if (nullable_subgraph($grammar, $q->start)) {
							$n++;
						}
						$q = ($q instanceof Alternative) ? $q->alt : null;
					}
					if ($n > 1) {
						echo "Ambiguous grammar in rule '$nt': at most one alternative must be nullable\n";
						$ok = false;
					}
				}
			}
			$ok &= ambiguity($grammar, $nt, $p->start, true);
			$ok &= ambiguity($grammar, $nt, $p->alt, false);
		} else if ($p instanceof Option) {
			if (empty_subgraph($p->start)) {
				echo "Ambiguous grammar in rule '$nt': contents of (...)? must not be empty\n";
				$ok = false;
			} else if (nullable_subgraph($grammar, $p->start)) {
				echo "Ambiguous grammar in rule '$nt': contents of (...)? must not be matched by empty sequence\n";
				$ok = false;
			}
			$ok &= ambiguity($grammar, $nt, $p->start, true);
		} else if ($p instanceof Iteration) {
			if (empty_subgraph($p->start)) {
				echo "Ambiguous grammar in rule '$nt': contents of (...)* or (...)+ must not be empty\n";
				$ok = false;
			} else if (nullable_subgraph($grammar, $p->start)) {
				echo "Ambiguous grammar in rule '$nt': contents of (...)* or (...)+ must not be matched by empty sequence\n";
				$ok = false;
			} else if (is_single_repetition($grammar, $p->start)) {
				echo "Ambiguous grammar in rule '$nt': contents of (...)* or (...)+ must not be matched by a single repitition\n";
				$ok = false;
			}
			$ok &= ambiguity($grammar, $nt, $p->start, true);
		}
		$p = $p->up ? null : $p->next;
	}
	return $ok;
}

function test_ambiguity($grammar) {
	$ok = true;
	foreach ($grammar->nonterm as $name => $nt) {
		$ok &= ambiguity($grammar, $name, $nt->ast, true);
	}
	return $ok;
}

function comp_first($grammar, $p, $visit) {
	$set = array();
	while ($p != null && $p->visited != $visit) {
		$p->visited = $visit;
		if ($p instanceof NonTerminal) {
			if ($grammar->nonterm[$p->name]->first === null) {
				$grammar->nonterm[$p->name]->first = comp_first($grammar, $grammar->nonterm[$p->name]->ast, $visit);
			}
			$set = $set + $grammar->nonterm[$p->name]->first;
		} else if ($p instanceof Terminal) {
			$set[$p->name] = 1;
//		} else if ($p instanceof Any) {
//            $set = $set + $sets[gn.p1]
		} else if ($p instanceof Alternative) {
            $set = $set + comp_first($grammar, $p->start, $visit);
            $set = $set + comp_first($grammar, $p->alt, $visit);
		} else if ($p instanceof Option || $p instanceof Iteration) {
            $set = $set + comp_first($grammar, $p->start, $visit);
		}
        if (!nullable_node($grammar, $p)) {
        	return $set;
		}
		$p = $p->next;
	}
	return $set;
}

function comp_first_set($grammar, $p) {
	return comp_first($grammar, $p, visit_number());
}

function comp_first_sets($grammar) {
	foreach ($grammar->nonterm as $name => $nt) {
		if ($nt->first === null) {
			$nt->first = comp_first_set($grammar, $nt->ast);
		}
	}
}

function comp_first_nt($grammar, $p, $visit) {
	$set = array();
	while ($p != null && $p->visited != $visit) {
		$p->visited = $visit;
		if ($p instanceof NonTerminal) {
			$set[$p->name] = 1;
		} else if ($p instanceof Alternative) {
            $set = $set + comp_first_nt($grammar, $p->start, $visit);
            $set = $set + comp_first_nt($grammar, $p->alt, $visit);
		} else if ($p instanceof Option || $p instanceof Iteration) {
            $set = $set + comp_first_nt($grammar, $p->start, $visit);
		}
        if (!nullable_node($grammar, $p)) {
        	return $set;
		}
		$p = $p->next;
	}
	return $set;
}

function test_left_recursion($grammar) {
  	$ok = true;
	$first_nt = array();
	foreach ($grammar->nonterm as $name => $nt) {
		$first_nt[$name] = comp_first_nt($grammar, $nt->ast, visit_number());
		if (isset($first_nt[$name][$name])) {
			echo "Direct left recursion '$name' -> '$name'\n";
			$ok = false;
		}
	}
	do {
		$changed = false;
		foreach ($grammar->nonterm as $nt => $dummy) {
			foreach ($first_nt[$nt] as $nt2 => $dummy) {
				foreach ($first_nt[$nt2] as $nt3 => $dummy) {
					if (!isset($first_nt[$nt][$nt3])) {
						$first_nt[$nt][$nt3] = 1;
						$changed = 1;
					}
				}
			}
		}
	} while ($changed);
	foreach ($grammar->nonterm as $nt => $dummy) {
		if (isset($first_nt[$nt][$nt])) {
			foreach ($first_nt[$nt] as $nt2 => $dummy) {
				if ($nt2 != $nt) {
					if (isset($first_nt[$nt2][$nt])) {
						echo "Indirect left recursion '$nt' -> '$nt2'\n";
						$ok = false;
					}
				}
			}
		}
	}
	return $ok;
}

function comp_follow($grammar, $nt, $p, $visit, &$follow_nt) {
	while ($p != null && $p->visited != $visit) { // > 0
		$p->visited = $visit;
		if ($p instanceof NonTerminal) {
			$grammar->nonterm[$p->name]->follow = $grammar->nonterm[$p->name]->follow + comp_first_set($grammar, $p->next);
			if ($nt != $p->name &&
			    nullable_graph($grammar, $p->up == 2 ? $p->next->next : $p->next)) {
				$follow_nt[$p->name][$nt] = 1;
			}
		} else if ($p instanceof Alternative) {
			comp_follow($grammar, $nt, $p->start, $visit, $follow_nt);
			comp_follow($grammar, $nt, $p->alt, $visit, $follow_nt);
		} else if ($p instanceof Option || $p instanceof Iteration) {
			comp_follow($grammar, $nt, $p->start, $visit, $follow_nt);
		}
        $p = $p->up ? null : $p->next;
	}
}

function complete($grammar, $nt0, $nt, $p, $visit, &$follow_nt) {
	if ($p->visited != $visit) {
		$p->visited = $visit;
		foreach ($grammar->nonterm as $name1 => $nt1) {
    	    if (isset($follow_nt[$nt][$name1])) {
				complete($grammar, $nt0, $name1, $nt1->ast, $visit, $follow_nt);
				$grammar->nonterm[$nt]->follow = $grammar->nonterm[$nt]->follow + $grammar->nonterm[$name1]->follow;
				if ($nt == $nt0) {
					unset($follow_nt[$nt][$name1]);
				}
			}
		}
	}
}

function comp_follow_sets($grammar) {
	$visit = visit_number();
	$follow_nt = array();
	$grammar->nonterm[$grammar->start]->follow = array("<EOF>" => 1);
	// TODO: allowing <EOF> may be incorrect ???
	foreach ($grammar->sub_start as $start) {
		$grammar->nonterm[$start]->follow = array("<EOF>" => 1);
	}
	foreach ($grammar->nonterm as $name => $nt) { /*get direct successors of nonterminals*/
 		comp_follow($grammar, $name, $nt->ast, $visit, $follow_nt);
	}

	foreach ($grammar->nonterm as $name => $nt) { /*add indirect successors to follow.ts*/
 		complete($grammar, $name, $name, $nt->ast, visit_number(), $follow_nt);
	}
}

function dump_first_follow($grammar) {
	echo "FIRST & FOLLOW\n";
	foreach ($grammar->nonterm as $name => $nt) {
		echo "$name\n";
		echo "\tFIRST:";
		$a = $nt->first;
		ksort($a);
		foreach ($a as $t => $dummy) {
			echo " $t";
		}
		echo "\n";
		echo "\tFOLLOW:";
		$a = $nt->follow;
		ksort($a);
		foreach ($a as $t => $dummy) {
			echo " $t";
		}
		echo "\n";
	}
}

function comp_expected($grammar, $p, $q) {
	$exp = comp_first_set($grammar, $p);
    if (nullable_graph($grammar, $p)) {
    	$exp = $exp + $grammar->nonterm[$q]->follow;
	}
	return $exp;
}

function ll_error($error_type, $cond, $nt, $name) {
	echo "$error_type in '$nt': ";
	if (!is_null($name)) {
		if (is_array($name)) {
			if (count($name) == 1) {
				foreach ($name as $n => $dummy) {
					echo "'$n' is ";
				}
			} else {
				foreach ($name as $n => $dummy) {
					echo "'$n' ";
				}
				echo "are ";
			}
		} else {
			echo "'$name' is ";
		}
	}
	if ($cond == 1) {
		echo "the start of several alternatives.\n";
	} else if ($cond == 2) {
        echo "the start & successor of nullable structure\n";
    } else if ($cond == 3) {
    	echo "an ANY node that matches no symbol\n";
    }
}

function check_set($s1, $s2, &$syms) {
	$ok = true;
	foreach ($s1 as $key => $val) {
		if (isset($s2[$key])) {
			$syms[$key] = 1;
			$ok = false;
//		    $conflicts[] = array($cond, $nt, $p, $key);
		}
	}
	return $ok;
}

function _get_first_syms($grammar, $p, $syms, $visit, $set) {
	while ($p != null && $p->visited != $visit) {
		$p->visited = $visit;
		if ($p instanceof NonTerminal) {
			$check = false;
			foreach ($syms as $sym => $dummy) {
				if (isset($grammar->nonterm[$p->name]->first[$sym])) {
					$check = true;
					break;
				}
			}
//			if ($check) {
				$set = _get_first_syms($grammar, $grammar->nonterm[$p->name]->ast, $syms, $visit, $set);
//			}
		} else if ($p instanceof Terminal) {
			if (isset($syms[$p->name])) {
				$set[$p->name][] = $p;
			}
//		} else if ($p instanceof Any) {
//            $set = $set + $sets[gn.p1]
		} else if ($p instanceof Alternative) {
			$q = $p;
			do {
	            $set = _get_first_syms($grammar, $q->start, $syms, $visit, $set);
				$q = $q->alt;
			} while ($q != null);
		} else if ($p instanceof Option || $p instanceof Iteration) {
            $set = _get_first_syms($grammar, $p->start, $syms, $visit, $set);
		}
        if (!nullable_node($grammar, $p)) {
        	return $set;
		}
		$p = $p->next;
	}
	return $set;
}

function get_first_syms($grammar, $p, $syms) {
	return _get_first_syms($grammar, $p, $syms, visit_number(), array());
}

function _get_expected_syms($grammar, $p, $nt, $syms, $visit, $set) {
	$set = _get_first_syms($grammar, $p, $syms, $visit, $set);
	if (nullable_graph($grammar, $p)) {
		if (!isset($grammar->nonterm[$nt]->visited) ||
		    $grammar->nonterm[$nt]->visited != $visit) {
			$grammar->nonterm[$nt]->visited = $visit;
			foreach ($grammar->nonterm[$nt]->occurance as $occurance) {
				list($nt1, $p1) = $occurance;
				$p1 = $p1->next;
				$set = _get_expected_syms($grammar, $p1, $nt1, $syms, $visit, $set);
			}
		}
	}
	return $set;
}

function get_expected_syms($grammar, $p, $nt, $syms) {
	return _get_expected_syms($grammar, $p, $nt, $syms, visit_number(), array());
}

function check_alternatives($grammar, $nt, $p, &$conflicts) {
	while ($p != null) {
		if ($p instanceof Alternative) {
			$q = $p;
			$s1 = array();
			$p->has_pred = 0;
			$ok = true;
			$conflict_syms = array();
			while ($q != null) { /*for all alternatives*/
				if ($q->start instanceof Predicate) {
					$p->has_pred++;
				} else {
					$s2 = comp_expected($grammar, $q->start, $nt);
					$syms = array();
					if (!check_set($s1, $s2, $syms)) {
						$ok = false;
						$conflict_syms += $syms;
					}
					$s1 = $s1 + $s2;
				}
				check_alternatives($grammar, $nt, $q->start, $conflicts);
				$q = $q->alt;
			}
			if (!$ok) {
			    $conflicts[] = array(1, $nt, $p, $conflict_syms);
			}
		} else if ($p instanceof Option || $p instanceof Iteration) {
			if (!($p->start instanceof Predicate)) {
				$s1 = comp_first_set($grammar, $p->start);
				$s2 = comp_expected($grammar, $p->next, $nt);
				$conflict_syms = array();
				if (check_set($s1, $s2, $conflict_syms)) {
					if ($p->greedy !== null) {
						echo "WARNING: " . ($p->greedy ? "greedy" : "ungreedy") . " specifier doesn't makes sense in $nt\n";
						$p->greedy = null;
					}
				} else if ($p->greedy !== null) {
					// TODO: check for greedy/ungreedy resolver
					$s1 = get_first_syms($grammar, $p->start, $conflict_syms);
					$s2 = get_expected_syms($grammar, $p->next, $nt, $conflict_syms);
					$ok = false;
					foreach ($conflict_syms as $sym => $dummy) {
						if (count($s1[$sym]) == count($s2[$sym])) {
							foreach ($s1[$sym] as $p1) {
								$ok = false;
								foreach ($s2[$sym] as $p2) {
									if ($p1 === $p2) {
										$ok = true;
										break;
									}
								}
								if (!$ok) {
									break;
								}
							}
						}
						if (!$ok) {
							break;
						}
					}
					if ($ok) {
						if (!$p->greedy) {
							echo "WARNING: ungreedy conflict resolution is not supported in $nt\n";
						}
					} else {
					    $conflicts[] = array(2, $nt, $p, $conflict_syms);
					}
				} else {
				    $conflicts[] = array(2, $nt, $p, $conflict_syms);
				}
			}
			check_alternatives($grammar, $nt, $p->start, $conflicts);
//		} else if ($p instanceof Any) {
//			get_set($p->start, $s1);
//			if (count($s1) == 0) {
//				ll_error(3, 0);
//			}
        }
        $p = $p->up ? null : $p->next;
	}
}

function x_final_closure($grammar, $start, $stack, &$alt, &$checked = array()) {
	if (count($stack) == 0) {
//???		$alt[$start] = $stack;
		foreach ($grammar->nonterm[$grammar->la_nfa->final[$start]->name]->occurance as $occurance) {
			list($nt, $node) = $occurance;
			nterm_nfa($grammar, $nt);
			$alt[$node->state] = $stack;
			x_closure($grammar, $node->state, $stack, $alt, $checked);
		}
	} else {
		$start = $stack[count($stack) - 1];
		unset($stack[count($stack) - 1]);
		if (is_array($start)) {
			foreach ($start as $s) {
				$alt[$s] = $stack;
				x_closure($grammar, $s, $stack, $alt, $checked);
			}
		} else {
			$alt[$start] = $stack;
			x_closure($grammar, $start, $stack, $alt, $checked);
		}
	}
}

function x_closure($grammar, $start, $stack, &$alt, &$checked = array()) {
	if (isset($checked[$start])) {
		return;
	}
	$checked[$start] = 1;
	if (isset($grammar->la_nfa->move[$start])) {
		foreach ($grammar->la_nfa->move[$start] as $state2 => $sym2) {
			if (strpos($sym2, "nt:") === 0) {
				$nt = substr($sym2, 3);
				$nt_start = nterm_nfa($grammar, $nt);
				if (isset($grammar->la_nfa->final[$state2]) &&
				    !isset($grammar->la_nfa->move[$state2])) {
					// tail call optimization
					$new_stack = $stack;
				} else {
					$new_stack = $stack;
					$new_stack[count($new_stack)] = $state2;
				}
				$alt[$nt_start] = $new_stack;
				x_closure($grammar, $nt_start, $new_stack, $alt, $checked);
			}
		}
	}
	if (isset($grammar->la_nfa->final[$start])) {
		x_final_closure($grammar, $start, $stack, $alt, $checked);
	}
}

function x_name($alts) {
	$name = "";
	ksort($alts);
	foreach ($alts as $n => $alt) {
		ksort($alt);
		$name .= "alt$n:";
		foreach ($alt as $state => $stack) {
			$name .= $state;
			if (count($stack) > 0) {
				$name .= '(';
				foreach ($stack as $context1) {
					if (is_array($context1)) {
						$name .= "(";
						foreach ($context1 as $context2) {
							$name .= "$context2,";
						}
						$name[strlen($name)-1] = ')';
						$name .= ",";						
					} else {
						$name .= "$context1,";
					}
				}
				$name[strlen($name)-1] = ')';
			}
			$name .= ",";
		}
		$name[strlen($name)-1] = "\n";
	}
	return $name;
}

function build_la_dfa($grammar, $start_stataes, $terms, $ctx_nt, &$ambiguous) {
	$ambiguous = false;
	$ctx = array();
	foreach ($grammar->nonterm[$ctx_nt]->occurance as $occurance) {
		list($nt, $node) = $occurance;
		nterm_nfa($grammar, $nt);
		$ctx[] = $node->state;
//		$alt[$node->state] = $stack;
//		x_closure($grammar, $node->state, $stack, $alt, $checked);
	}
//	$initial_stack = (count($ctx) == 0) ? array() : array($ctx);
	 
	$alts = array();
	foreach ($start_stataes as $start => $dummy) {
		if (isset($grammar->la_nfa->move[$start])) {
			foreach ($grammar->la_nfa->move[$start] as $state => $sym) {
				$alts[$state][$start] = array($ctx);
				if (strpos($sym, "nt:") === 0) {
					$nt = substr($sym, 3);
					$nt_start = nterm_nfa($grammar, $nt);
					$stack = array($ctx, $state);
					$alts[$state][$nt_start] = $stack;
					x_closure($grammar, $nt_start, $stack, $alts[$state]);
				}
			}
		}
		if (isset($grammar->la_nfa->final[$start])) {
			$alts[-1] = array();
			x_final_closure($grammar, $start, array(), $alts[-1]);
		}
	}
	$name = x_name($alts);

	$dfa = new FA;
	$n = 1;
	$dstates[0] = $alts;
	$states = array($name => 0);
	if (DUMP_LA_NFA_DFA) {
		echo "0\n$name\n";
	}

	for ($i = 0; $i < $n; $i++) {
		if (count($dstates[$i]) == 1) {
			reset($dstates[$i]);
			$dfa->final[$i] = key($dstates[$i]);
		} else {
			$syms = array();
			foreach ($dstates[$i] as $k => $alt) {
				foreach ($alt as $state => $stack) {
					if (isset($grammar->la_nfa->move[$state])) {
						foreach ($grammar->la_nfa->move[$state] as $target => $sym) {
							if (strpos($sym, "nt:") !== 0) {
								// FIXME: condition might be not enoght ???
								if (!isset($start_stataes[$state]) || $target == $k || $k == -1) {
									$syms[$sym][$k][$target] = $stack;
									x_closure($grammar, $target, $stack, $syms[$sym][$k]);
								}
							}
						}
			    	}
				}
			}
			foreach ($syms as $sym => $alts) {
				$name = x_name($alts);
				// Check for ambiguity
				if (count($alts) > 1) {
					foreach ($alts as $k1 => $states1) {
						foreach ($alts as $k2 => $states2) {
							if ($k1 != $k2) {
								if (count($states1) == count($states2)) {
									if ($states1 == $states2) {
										if (DUMP_LA_NFA_DFA) {
											echo "$n-$sym [ambiguous]\n$name\n";
										}
										$ambiguous = true;
										return null;
									}
								}
							}
						}
					}
				}
				if (isset($states[$name])) {
					$j = $states[$name];
				} else {
					$j = $n;
					$states[$name] = $n;
					$dstates[$n] = $alts;
					if (DUMP_LA_NFA_DFA) {
						echo "$n-$sym\n$name\n";
					}
					$n++;
				}
				$dfa->move[$i][$j][] = $sym;
			}
		}
		unset($dstates[$i]); // free memory
		if ($n > 100) {
			return null;
		}
	}
	$dfa->n = $n;

	return $dfa;
}

function calc_follow2($p, $state, &$first, &$last, &$nullable, $nfa) {
	if ($p instanceof Terminal) {
		$p->state = $n = ++$nfa->n;
		$first = array($n => $p->name);
		$last = array($n => $p->name);
		$nullable = false;
	} else if ($p instanceof NonTerminal) {
		$p->state = $n = ++$nfa->n;
		$first = array($n => "nt:" . $p->name);
		$last = array($n => "nt:" . $p->name);
		$nullable = false;
	} else {
		$p->state = $state;
		if ($p instanceof Alternative) {
			$first = array();
			$last = array();
			$nullable = false;
			$q = $p;
			do {
				calc_follow2($q->start, $state, $sub_first, $sub_last, $sub_nullable, $nfa);
				if ($sub_nullable) {
					$nullable = true;
				}
				$first += $sub_first;
				$last += $sub_last;
				$q = $q->alt;
			} while ($q);
		} else if ($p instanceof Option) {
			calc_follow2($p->start, $state, $first, $last, $sub_nullable, $nfa);
//			if ($p->greedy === false) {
//				foreach ($first as $n => $dummy) {
//					$nfa->ungreedy[$n] = 1;
//				}
//			}
			$nullable = true;
		} else if ($p instanceof Iteration) {
			calc_follow2($p->start, $state, $first, $last, $sub_nullable, $nfa);
//			if ($p->greedy === false) {
//				foreach ($first as $n => $dummy) {
//					$nfa->ungreedy[$n] = 1;
//				}
//			}
			if ($p->min_count == 0) {
				$nullable = true;
			} else {
				$nullable = $sub_nullable;
				// store to determine the choice point states
				$p->last = $last;
			}
			foreach ($last as $f => $x) {
				foreach ($first as $l => $y) {
					$nfa->move[$f][$l] = $y;
				}
			}
		} else { /* Epsilon, Action, etc */
		    $first = array();
		    $last = array();
			$nullable = true;
		}
	}
	if ($p->next !== null && !$p->up) {
		$state = isset($p->state) ? $p->state : $state;
		calc_follow2($p->next, $state, $sub_first, $sub_last, $sub_nullable, $nfa);
		foreach ($last as $f => $x) {
			foreach ($sub_first as $l => $y) {
				$nfa->move[$f][$l] = $y;
			}
		}
		if ($nullable) {
			$first += $sub_first;
		}
		if ($sub_nullable) {
			$last += $sub_last;
		} else {
			$last = $sub_last;
			$nullable = false;
		}
	}
}

function nterm_nfa($grammar, $nterm) {
	if ($grammar->nonterm[$nterm]->la_nfa === null) {
		if ($grammar->la_nfa === null) {
			$grammar->la_nfa = new FA;
			// Add fictive .start nonterminal defined as start followed by EOF
			$start = new NonTerminal($grammar->start);
			$start->next = new Terminal('<EOF>');
			$grammar->nonterm['.start'] = new NonTermDef('.start', null, null, $start);
			$grammar->nonterm[$grammar->start]->occurance[] = array('.start', $start);
		}
		$init = $grammar->la_nfa->n;
		$nfa = new FA();
		$nfa->start = $nfa->n = $init;
		calc_follow2($grammar->nonterm[$nterm]->ast, $nfa->start, $first, $last, $nullable, $nfa);
//		if (ALT_DFA) {
//			$last_state = ++$nfa->n;
//			$nfa->final[$last_state] = $grammar->nonterm[$nterm];
//			foreach ($last as $l => $x) {
//				$nfa->move[$l][$last_state] = null;
//			}
//			if ($nullable) {
//				$nfa->move[$init][$last_state] = null;
//			}
//		} else {
			foreach ($last as $l => $x) {
				$nfa->final[$l] = $grammar->nonterm[$nterm];
			}
			if ($nullable) {
				$nfa->final[$init] = $grammar->nonterm[$nterm];
			}
//		}
		foreach ($first as $l => $x) {
			$nfa->move[$init][$l] = $x;
		}
		$grammar->nonterm[$nterm]->la_nfa = $init;
		if (DUMP_LA_NFA) {
			dump_fa($grammar, "LA NFA $nterm", $nfa, true);
		}
		if (DUMP_DOT_LA_NFA) {
			la_fa_to_dots($grammar, $nterm . "_nfa", $nfa, true, false);
		}
		$grammar->la_nfa->n = $nfa->n + 1;
		$grammar->la_nfa->move  += $nfa->move;
		$grammar->la_nfa->final += $nfa->final;
	}
	return $grammar->nonterm[$nterm]->la_nfa;
}

function try_to_resolve($grammar, $nt, $p, $syms, &$ambiguous) {
	nterm_nfa($grammar, $nt);
	if ($p instanceof Iteration && $p->min_count > 0) {
		// FIXME: get the proper state(s) ???
		$states = $p->last;
	} else {
		$states = array($p->state => 1);
	}
	foreach ($states as $state => $dummy) {
		if (isset($grammar->la_dfa[$state])) {
			$dfa = $grammar->la_dfa[$state];
			foreach ($states as $state => $dummy) {
				if (!isset($grammar->la_dfa[$state])) {
					$grammar->la_dfa[$state] = $dfa;
				}
			}
			return true;
		}
	}
	$dfa = build_la_dfa($grammar, $states, $syms, $nt, $ambiguous);
	if ($dfa !== null) {
		if (DUMP_LA_DFA) {
			$t_states = "";
			foreach ($states as $state => $dummy) {
				$t_states .= "$state, ";
			}
			$t_states = substr($t_states, 0, strlen($t_states)-2);
			dump_fa($grammar, "LA DFA $nt($t_states)", $dfa, true);
		}
		if (DUMP_DOT_LA_DFA) {
			la_fa_to_dots($grammar, $nt . "_dfa_" . $p->state, $dfa, true, $p->state);
		}
		minimize_dfa($dfa);
		if (DUMP_LA_MIN_DFA) {
			$t_states = "";
			foreach ($states as $state => $dummy) {
				$t_states .= "$state, ";
			}
			$t_states = substr($t_states, 0, strlen($t_states)-2);
			dump_fa($grammar, "LA mDFA $nt($t_states)", $dfa, true);
		}
		if (DUMP_DOT_LA_MIN_DFA) {
			la_fa_to_dots($grammar, $nt . "_min_dfa_" . $p->state, $dfa, true, $p->state);
		}
		foreach ($states as $state => $dummy) {
			if (!isset($grammar->la_dfa[$state])) {
				$grammar->la_dfa[$state] = $dfa;
			}
		}
		$grammar->nonterm[$nt]->la_states[$state] = 1;
		return true;
	}
	return false;
}

function test_ll1($grammar) {
  	$conflicts = array();
	foreach ($grammar->nonterm as $name => $nt) {
		check_alternatives($grammar, $name, $nt->ast, $conflicts);
	}
	foreach ($conflicts as $conflict) {
		$cond = $conflict[0];
		$nt = $conflict[1];
		$p = $conflict[2];
		$syms = $conflict[3];
		if (LL1) {
			ll_error("LL(1) error", $cond, $nt, $syms);
		} else if (!try_to_resolve($grammar, $nt, $p, $syms, $ambiguous)) {
			// TODO: $ambiguous ? "Ambiguous grammar" : "LL(R) error"
			ll_error("LL(R) error", $cond, $nt, $syms);
		} else {
			if (VERBOSE) {
				ll_error("LL(1) warning", $cond, $nt, $syms);
			}
		}
	}
}

// FA
function calc_follow($p, &$first, &$last, &$nullable, $nfa) {
	if ($p instanceof Character) {
	    $n = ++$nfa->n;
	    $first = array($n => $p->ch);
	    $last = array($n => $p->ch);
	    $nullable = false;
		if (isset($p->ctx)) {
			$nfa->ctx[$n] = true;
		}
	} else if ($p instanceof Charset) {
	    $n = ++$nfa->n;
	    $first = array($n => array());
	    $last = array($n => array());
	    $nullable = false;
		if (isset($p->ctx)) {
			$nfa->ctx[$n] = true;
		}
		if ($p->neg) {
			for ($i = 0; $i < 256; $i++) {
				$ch = chr($i);
				if (!isset($p->set[$ch])) {
			        $first[$n][] = $ch;
				    $last[$n][] = $ch;
				}
 			}
		} else {
			foreach ($p->set as $ch => $x) {
		        $first[$n][] = $ch;
			    $last[$n][] = $ch;
			}
		}
	} else if ($p instanceof Alternative) {
	    $first = array();
	    $last = array();
		$nullable = false;
		$q = $p;
		do {
			calc_follow($q->start, $sub_first, $sub_last, $sub_nullable, $nfa);
			if ($sub_nullable) {
				$nullable = true;
			}
			$first += $sub_first;
			$last += $sub_last;
			$q = $q->alt;
		} while ($q);
	} else if ($p instanceof Option) {
		calc_follow($p->start, $first, $last, $sub_nullable, $nfa);
		if ($p->greedy === false) {
			foreach ($first as $n => $dummy) {
				$nfa->ungreedy[$n] = 1;
			}
		}
		$nullable = true;
	} else if ($p instanceof Iteration) {
		calc_follow($p->start, $first, $last, $sub_nullable, $nfa);
		if ($p->greedy === false) {
			foreach ($first as $n => $dummy) {
				$nfa->ungreedy[$n] = 1;
			}
		}
		if ($p->min_count == 0) {
			$nullable = true;
		} else {
			$nullable = $sub_nullable;
		}
		foreach ($last as $f => $x) {
			foreach ($first as $l => $y) {
				$nfa->move[$f][$l] = $y;
			}
		}
	} else { /* Epsilon, Action, etc */
	    $first = array();
	    $last = array();
		$nullable = true;
	}
	if ($p->next !== null && !$p->up) {
		calc_follow($p->next, $sub_first, $sub_last, $sub_nullable, $nfa);
		foreach ($last as $f => $x) {
			foreach ($sub_first as $l => $y) {
				$nfa->move[$f][$l] = $y;
			}
		}
		if ($nullable) {
			$first += $sub_first;
		}
		if ($sub_nullable) {
			$last += $sub_last;
		} else {
			$last = $sub_last;
			$nullable = false;
		}
	}
}

function check_dfa_ambigouoty($terminals) {
	$ok = true;
	$terminal = null;
	$conflicts = 0;
	$first = null;
	foreach ($terminals as $x) {
		if ($x instanceof RegExp) {
			if ($conflicts == 0) {
				$first = $x->name;
			} else if ($conflicts == 1) {
				echo "Ambigouos symbols $first and $x->name";
				$ok = false;
			} else {
				echo " and $x->name";
			}
			$conflicts++;
		} else {
/*			// FIXME:
			if ($conflicts == 0) {
				$first = $x->name;
			} else if ($conflicts == 1) {
				echo "Ambigouos symbols $first and $x->name";
				$ok = false;
			} else {
				echo " and $x->name";
			}
			$conflicts++;
*/
			$terminal = $x;
		}
	}
	if ($conflicts > 1) {
		echo "\n";
	}
	return $ok ? $terminal : null;
}

function build_alt_dfa($nfa) {
	if (DUMP_NFA_DFA) {
		echo "NFA->DFA\n";
		echo $nfa->start . ": 0\n";
	}

	$dfa = new FA;
	$n = 1;
	$dstates[0] = $nfa->move[$nfa->start];

	$name = "";
	$start = $dstates[0];
	ksort($start);
	foreach ($start as $s => $dummy) {
		$name .= "_$s";
	}
	$states = array($name => 0);

	for ($i = 0; $i < $n; $i++) {
		$char = array();
		foreach ($dstates[$i] as $state => $ch) {
			if ($ch !== null) {
				if (is_array($ch)) {
					foreach ($ch as $c) {
						if (!isset($char[$c])) {
							$char[$c] = $nfa->move[$state];
						} else {
							$char[$c] += $nfa->move[$state];
						}
					}
				} else {
					if (!isset($char[$ch])) {
						$char[$ch] = $nfa->move[$state];
					} else {
						$char[$ch] += $nfa->move[$state];
					}
				}
			}
		}
		foreach ($char as $ch => $move) {
			if (!is_string($ch)) {
				$ch = (string)$ch;
			}

			$name = "";
			$state_name = null;
			$ungreedy = false;
			$ctx = false;
			ksort($move);
			foreach ($move as $s => $x) {
				$name .= "_$s";
				if (isset($nfa->final[$s])) {
					if ($state_name !== null) {
						if (is_array($state_name)) {
							$state_name[] = $nfa->final[$s];
						} else {
							$state_name = array($state_name, $nfa->final[$s]);
						}
					} else {
						$state_name = $nfa->final[$s];
					}
				}
				if (isset($nfa->ungreedy[$s])) {
					$ungreedy = true;
				}
				if (isset($nfa->ctx[$s])) {
					$ctx = true;
				}
			}
			if (isset($states[$name])) {
				$j = $states[$name];
			} else {
				if ($state_name !== null) {
					if (is_array($state_name)) {
						$state_name = check_dfa_ambigouoty($state_name);
						if ($state_name === null) {
							return null;
						}
					}
					$dfa->final[$n] = $state_name;
				}
				$j = $n;
				$states[$name] = $n;
				$dstates[$n] = $move;
				if ($ungreedy) {
					$dfa->ungreedy[$n] = true;
				}
				if ($ctx) {
					$dfa->ctx[$n] = true;
				}
				if (DUMP_NFA_DFA) {
					echo "$n:";
					foreach ($move as $s => $x) {
						echo " $s";
						if (isset($nfa->ungreedy[$s])) {
							echo "u";
						}
					}
					echo " \n";
				}
				$n++;
			}
			$dfa->move[$i][$j][] = $ch;
		}
		unset($dstates[$i]); // free memory
	}
	if (count($dfa->ungreedy) > 0) {
		/* remove transitions from finish states to non ungreedy states */
		foreach ($dfa->final as $state => $dummy) {
			if (isset($dfa->move[$state])) {
				foreach ($dfa->move[$state] as $state2 => $dummy2) {
					if (isset($dfa->ungreedy[$state2])) {
						unset($dfa->move[$state][$state2]);
					}
				}
				if (count($dfa->move[$state]) == 0) {
					unset($dfa->move[$state]);
				}
			}
		}
	}
	$dfa->n = $n;

	return $dfa;
}

function build_dfa($nfa) {
	if (DUMP_NFA_DFA) {
		echo "NFA->DFA\n";
		echo $nfa->start . ": 0\n";
	}

	$dfa = new FA;
	$dfa->ctx = array();
	$n = 1;
	$dstates[0] = array($nfa->start=>1);
	$states = array("_" . $nfa->start => 0);

	for ($i = 0; $i < $n; $i++) {
		$char = array();
		foreach ($dstates[$i] as $state => $dummy) {
			if (isset($nfa->move[$state])) {
				foreach ($nfa->move[$state] as $target => $ch) {
					if ($ch !== null) {
						if (is_array($ch)) {
							foreach ($ch as $c) {
								$char[$c][$target] = 1;
							}
						} else {
							$char[$ch][$target] = 1;
						}
					}
				}
			}
		}
		foreach ($char as $ch => $targets) {
			if (!is_string($ch)) {
				$ch = (string)$ch;
			}

			$name = "";
			$state_name = null;
			$ctx = false;
			$ungreedy = false;
			ksort($targets);
			foreach ($targets as $s => $dummy) {
				$name .= "_$s";
				if (isset($nfa->final[$s])) {
					if ($state_name !== null) {
						if (is_array($state_name)) {
							$state_name[$nfa->final[$s]->name] = $nfa->final[$s];
						} else if ($state_name->name != $nfa->final[$s]->name) {
							$state_name = array(
							  $state_name->name => $state_name,
							  $nfa->final[$s]->name => $nfa->final[$s]);
						}
					} else {
						$state_name = $nfa->final[$s];
					}
				}
				if (isset($nfa->ungreedy[$s])) {
					$ungreedy = true;
				}
				if (isset($nfa->ctx[$s])) {
					$ctx = true;
				}
			}
			if (isset($states[$name])) {
				$j = $states[$name];
			} else {
				if ($state_name !== null) {
					if (is_array($state_name)) {
						$state_name = check_dfa_ambigouoty($state_name);
						if ($state_name === null) {
							return null;
						}
					}
					$dfa->final[$n] = $state_name;
				}
				$j = $n;
				$states[$name] = $n;
				$dstates[$n] = $targets;
				if ($ungreedy) {
					$dfa->ungreedy[$n] = true;
				}
				if ($ctx) {
					$dfa->ctx[$n] = true;
				}
				if (DUMP_NFA_DFA) {
					echo "$n:";
					foreach ($targets as $s => $x) {
						echo " $s";
						if (isset($nfa->ungreedy[$s])) {
							echo "u";
						}
					}
					echo " \n";
				}
				$n++;
			}
			$dfa->move[$i][$j][] = $ch;
		}
		unset($dstates[$i]); // free memory
	}
	if (count($dfa->ungreedy) > 0) {
		/* remove transitions from finish states to non ungreedy states */
		foreach ($dfa->final as $state => $dummy) {
			if (isset($dfa->move[$state])) {
				foreach ($dfa->move[$state] as $state2 => $dummy2) {
					if (isset($dfa->ungreedy[$state2])) {
						unset($dfa->move[$state][$state2]);
					}
				}
				if (count($dfa->move[$state]) == 0) {
					unset($dfa->move[$state]);
				}
			}
		}
	}
	$dfa->n = $n;

	return $dfa;
}

function minimize_dfa($dfa) {
	$groups = array();
	$map = array();
	foreach($dfa->move as $state => $target) {
		if (!isset($map[$state])) {
			if (isset($dfa->final[$state])) {
				$groups[1][] = $state;
				$map[$state] = 1;
			} else {
				$groups[0][] = $state;
				$map[$state] = 0;
			}
		}
		foreach ($target as $state => $charset) {
			if (!isset($map[$state])) {
				if (isset($dfa->final[$state])) {
					$groups[1][] = $state;
					$map[$state] = 1;
				} else {
					$groups[0][] = $state;
					$map[$state] = 0;
				}
			}
		}
	}
	do {
		$changed = false;
		$n = count($groups);
		for ($k= 0; $k < $n; $k++) {
			$group = $groups[$k];
			$m = count($group);
			if ($m != 1) {
				$sub_groups = array(array($group[0]));
				for ($i = 1; $i < $m; $i++) {
					$state = $group[$i];
					$consumed = false;
					for ($j = 0; $j < count($sub_groups); $j++) {
						$sub_group_state = $sub_groups[$j][0];
						if (isset($dfa->final[$state]) && $dfa->final[$state] !== $dfa->final[$sub_group_state]) {
							continue;
						}
						if (isset($dfa->ctx[$state]) !== isset($dfa->ctx[$sub_group_state])) {
							continue;
						}
						if (isset($dfa->move[$state]) != isset($dfa->move[$sub_group_state])) {
							continue;
						}
						if (!isset($dfa->move[$state]) && !isset($dfa->move[$sub_group_state])) {
							$sub_groups[$j][] = $state;
							$consumed = true;
							break;
						} else if (count($dfa->move[$state]) == count($dfa->move[$sub_group_state])) {
							$m1 = array();
							foreach($dfa->move[$state] as $s => $v) {
								$m1[$map[$s]] = $v;
							}
							$m2 = array();
							foreach($dfa->move[$sub_group_state] as $s => $v) {
								$m2[$map[$s]] = $v;
							}
							$eq = true;
							foreach ($m1 as $s => $v) {
								if (!isset($m2[$s]) || $m2[$s] != $v) {
									$eq = false;
									break;
								}
							}
							if ($eq) {
								$sub_groups[$j][] = $state;
								$consumed = true;
								break;
							}
						}
					}
					if (!$consumed) {
						$sub_groups[] = array($state);
					}
				}
				if (count($sub_groups) > 1) {
					$groups[$k] = $sub_groups[0];
					for ($i = 1; $i < count($sub_groups); $i++) {
						$j = count($groups);
						$groups[$j] = $sub_groups[$i];
						foreach ($groups[$j] as $state) {
							$map[$state] = $j;
						}
					}
					$changed = true;
				}
			}
		}
	} while ($changed);
	$map = array();
	foreach ($groups as $group) {
		if (count($group) > 1) {
			for ($i = 1; $i < count($group); $i++) {
				$map[$group[$i]] = $group[0];
			}
		}
	}
	if (count($map) > 0) {
		foreach ($map as $state => $new_state) {
			unset($dfa->move[$state]);
			unset($dfa->final[$state]);
			if (isset($dfa->ctx[$state])) {
				unset($dfa->ctx[$state]);
				$dfa->ctx[$new_state] = true;
			}
			foreach ($dfa->move as &$v) {
				if (isset($v[$state])) {
					if (isset($v[$map[$state]])) {
						$v[$map[$state]] = array_merge($v[$map[$state]], $v[$state]);
					} else {
						$v[$map[$state]] = $v[$state];
					}
					unset($v[$state]);
				}
			}
		}
		// TODO: renumber states to be in range from 0 to n
		//$dfa->n = count($groups);
	}
}

function collect_first_terms($grammar, $node, $visit, &$terms) {
	while ($node != null && $node->visited != $visit) {
		$node->visited = $visit;
		if ($node instanceof Terminal) {
			$terms[$node->name] = $node;
		} else if ($node instanceof NonTerminal) {
			collect_first_terms($grammar, $grammar->nonterm[$node->name]->ast, $visit, $terms);
		} else if ($node instanceof Alternative) {
			collect_first_terms($grammar, $node->start, $visit, $terms);
			collect_first_terms($grammar, $node->alt, $visit, $terms);
		} else if ($node instanceof Option) {
			collect_first_terms($grammar, $node->start, $visit, $terms);
		} else if ($node instanceof Iteration) {
			collect_first_terms($grammar, $node->start, $visit, $terms);
		} else if ($node instanceof SyntaticPredicate) {
			collect_first_terms($grammar, $node->start, $visit, $terms);
		}
        if (!nullable_node($grammar, $node)) {
        	return;
		}
		$node = $node->up ? null : $node->next;
	}
}

function collect_terms($grammar, $node, $visit, $scan, &$terms) {
	while ($node != null && $node->visited != $visit) {
		$node->visited = $visit;
		if ($node instanceof Terminal) {
			$terms[$node->name] = $node;
		} else if ($node instanceof NonTerminal) {
			// FIXME: What if the same term is used withseveral scanners
		    if ($grammar->nonterm[$node->name]->lexer === null) {
				if ($grammar->nonterm[$node->name]->use_lexer === null) {
					$grammar->nonterm[$node->name]->use_lexer = $scan;
					collect_terms($grammar, $grammar->nonterm[$node->name]->ast, $visit, $scan, $terms);
				}
			} else {
				// Duplicate first terms from another scanner
				collect_first_terms($grammar, $grammar->nonterm[$node->name]->ast, $visit, $terms);
			}
		} else if ($node instanceof Alternative) {
			collect_terms($grammar, $node->start, $visit, $scan, $terms);
			collect_terms($grammar, $node->alt, $visit, $scan, $terms);
		} else if ($node instanceof Option) {
			collect_terms($grammar, $node->start, $visit, $scan, $terms);
		} else if ($node instanceof Iteration) {
			collect_terms($grammar, $node->start, $visit, $scan, $terms);
		} else if ($node instanceof SyntaticPredicate) {
			collect_terms($grammar, $node->start, $visit, $scan, $terms);
		}
		$node = $node->up ? null : $node->next;
	}
}

function nfa_add_term($nfa, $p, $cs) {
	if ($p instanceof RegExp) {
		$q = $p->regexp;
	} else {
		/* Convert string into simple RegExp */
		$i = 0;
		$m = strlen($p->name);
		$ch = $p->name[$i++];
		if (!$cs && $ch >= 'A' && $ch <= 'Z') {
			$q = $r = new Charset(false, array($ch=>1, chr(ord($ch)-ord('A')+ord('a'))=>1));
		} else if (!$cs && $ch >= 'a' && $ch <= 'z') {
			$q = $r = new Charset(false, array($ch=>1, chr(ord($ch)-ord('a')+ord('A'))=>1));
		} else {
			$q = $r = new Character($ch);
		}
		while ($i < $m) {
			$ch = $p->name[$i++];
			if (!$cs && $ch >= 'A' && $ch <= 'Z') {
				$r->next = new Charset(false, array($ch=>1, chr(ord($ch)-ord('A')+ord('a'))=>1));
			} else if (!$cs && $ch >= 'a' && $ch <= 'z') {
				$r->next = new Charset(false, array($ch=>1, chr(ord($ch)-ord('a')+ord('A'))=>1));
			} else {
				$r->next = new Character($ch);
			}
			$r = $r->next;
		}
	}
	calc_follow($q, $first, $last, $nullable, $nfa);
	if (ALT_DFA) {
		$last_state = ++$nfa->n;
		$nfa->final[$last_state] = $p;
		foreach ($last as $l => $dummy) {
			$nfa->move[$l][$last_state] = null;
		}
		if ($nullable) {
			$nfa->move[0][$last_state] = null;
		}
	} else {
		foreach ($last as $l => $dummy) {
			$nfa->final[$l] = $p;
		}
//???		if ($nullable) {
//???			$nfa->final[$init] = $p;
//???		}
	}
	foreach ($first as $l => $x) {
		$nfa->move[0][$l] = $x;
	}
}

function dump_fa($grammar, $name, $fa, $term=false) {
	echo "$name\n";
	ksort($fa->move);
	// FIXME: Start symbol can be final
	if (isset($fa->final[$fa->start])) {
		if (is_object($fa->final[$fa->start])) {
			echo $fa->start . ": => " . $fa->final[$fa->start]->name . "\n";
		} else {
			echo $fa->start . ": => " . $fa->final[$fa->start] . "\n";
		}
	}
	foreach ($fa->move as $s1 => $v) {
		ksort($v);
		foreach ($v as $s2 => $s) {
			echo "$s1 -> $s2: ";
			if (isset($fa->ctx[$s2])) {
				echo "CTX$";
			}
			if (is_array($s)) {
				if ($term && count($s) === 1) {
					foreach ($s as $c) {
						if ($c instanceof RegExp) {
							echo $s->name;
						} else if ($grammar->term[$c]->special) {
							echo "<" . $grammar->term[$c]->name . ">";
						} else {
							echo "'$c'";
						}
					}
				} else {
					$first = true;
					foreach ($s as $c) {
						if ($s instanceof RegExp) {
							if ($first) {
								echo "{";
								$first = false;
							} else {
								echo ",";
							}
							echo $s->name;
						} else { /* string */
							if ($term) {
								if ($first) {
									echo "{";
									$first = false;
								} else {
									echo ",";
								}
								if ($grammar->term[$c]->special) {
									echo "<" . $grammar->term[$c]->name . ">";
								} else {
									echo "'$c'";
								}
							} else {
								$i = ord($c);
								if ($i < 32 || $i >= 127) {
									echo "\\" .
										chr((($i >> 6) & 3) + ord('0')) .
										chr((($i >> 3) & 7) + ord('0')) .
										chr(($i & 7) + ord('0'));
								} else {
									echo $c;
								}
							}
						}
					}
					if (!$first) {
						echo "}";
					}
				}
			} else if ($s !== null) {
				if ($s instanceof RegExp) {
					echo $s->name;
				} else { /* string */
					if ($term) {
						echo "'$s'";
					} else {
						echo $s;
					}
				}
			}
			if (isset($fa->final[$s2])) {
				if ($s !== null) {
					echo " ";
				}
				if (is_object($fa->final[$s2])) {
					echo "=> " . $fa->final[$s2]->name;
				} else {
					echo "=> " . $fa->final[$s2];
				}
			}
			echo "\n";
		}
	}
}

function build_nfa($grammar, $nterm, $scan) {
	$term = array();
	$n = visit_number();
	$grammar->nonterm[$nterm]->use_lexer = $scan;
	collect_terms($grammar, $grammar->nonterm[$nterm]->ast, $n, $scan, $term);
	if ($scan->skip != null) {
		foreach ($scan->skip as $name => $node) {
			$grammar->nonterm[$name]->use_lexer = $scan;
			collect_terms($grammar, $grammar->nonterm[$name]->ast, $n, $scan, $term);
		}
	}
	$nfa = new FA();
	$nfa->ctx = array();
	foreach ($term as $p) {
		nfa_add_term($nfa, $p, $grammar->case_sensetive);
	}
	// Add follow symbols
	foreach ($scan->follow as $t => $dummy) {
		if (!isset($term[$t]) && isset($grammar->term[$t]) && !$grammar->term[$t]->special) {
			nfa_add_term($nfa, $grammar->term[$t], $grammar->case_sensetive);
		}
	}
	// Add <EOF> as a follow symbol
	if (ADD_EOF) {
		if (ALT_DFA) {
			$eof_start = ++$nfa->n;
			$eof_end = ++$nfa->n;
			$nfa->move[0][$eof_start] = "<EOF>";
			$nfa->final[$eof_end] = new Terminal("<EOF>");
			$nfa->move[$eof_start][$eof_end] = null;
		} else {
			$eof_state = ++$nfa->n;
			$nfa->move[0][$eof_state] = "<EOF>";
			$nfa->final[$eof_state] = new Terminal("<EOF>");
		}
	}
	return $nfa;
}

function all_final($dfa, $s1, $tunnel) {
	if (isset($dfa->move[$s1])) {
		foreach ($dfa->move[$s1] as $s2 => $sym) {
			if (!isset($dfa->final[$s2]) &&
			    (!isset($tunnel[$s2]) || !isset($dfa->final[$tunnel[$s2]]))) {
				return false;
			}
		}
	}		
	if (isset($tunnel[$s1]) && !isset($dfa->final[$tunnel[$s1]])) {
		return false;
	}
	return true;
}

function scanner_check_state_backtracking($dfa, $s1, $tunnel, &$bt) {
	if (isset($dfa->move[$s1])) {
		foreach ($dfa->move[$s1] as $s2 => $dummy) {
			if (isset($dfa->final[$s2]) && $dfa->final[$s2] !== false && !all_final($dfa, $s2, $tunnel)) {
				$bt[$s2] = $dfa->final[$s2]->name;
			}
		}
	}
}


function scanner_check_backtracking($dfa, $tunnel) {
	$bt = array();
	foreach ($dfa->move as $s1 => $dummy) {
		if (isset($dfa->final[$s1]) && $dfa->final[$s1] !== false && !all_final($dfa, $s1, $tunnel)) {
			$bt[$s1] = $dfa->final[$s1]->name;
		}
		scanner_check_state_backtracking($dfa, $s1, $tunnel, $bt);
	}
	return $bt;
}

function emit_scanner_state($f, $dfa, $s1, $v, $states, $tunnel, $bt) {
	if (count($v) == 0 && isset($tunnel[$s1]) && isset($dfa->final[$s1])) {
		$f->scanner_state_tunnel_accept($tunnel[$s1], $dfa->final[$s1]->name);
	} elseif (count($v) == 1 && isset($tunnel[$s1])) {
		foreach ($v as $s2 => $s) {
			if (isset($dfa->final[$s1])) {
				$f->scanner_state_tunnel_condition($s, $tunnel[$s1], $dfa->final[$s1]->name);
			} else {
				$f->scanner_state_tunnel_condition($s, $tunnel[$s1], null);
			}
			if (isset($dfa->move[$s2])) {
				if (SCANNER_INLINE && $s1 != $s2 && $states[$s2] == 1) {
					$f->scanner_inline_state_start(count($dfa->move[$s2]) == 0 && isset($tunnel[$s2]) && isset($dfa->final[$s2]), isset($bt[$s2]) ? $bt[$s2] : null, isset($dfa->ctx[$s2]), isset($dfa->ctx[$s1]));
					emit_scanner_state($f, $dfa, $s2, $dfa->move[$s2], $states, $tunnel, $bt);
				} else {
					$f->scanner_state_transition($s1, $s2, isset($dfa->ctx[$s2]), isset($dfa->ctx[$s1]));
				}
			} else {
				if (isset($dfa->final[$s2])) {
					$f->scanner_state_accept($dfa->final[$s2]->name, isset($dfa->ctx[$s2]));
				} else {
					error("???");
				}
			}
		}
	} else {
		$first = true;
	 	$use_switch = SCANNER_SWITCH ? count($v) > $f::IF_VS_CASE : false;
		if ($use_switch) {
			$f->scanner_state_switch_start();
		}
		foreach ($v as $s2 => $s) {
			$f->scanner_state_condition($first, $s, $use_switch, $dfa->n);
			$first = false;
			if (isset($dfa->move[$s2])) {
				if (SCANNER_INLINE && $s1 != $s2 && $states[$s2] == 1) {
					$f->scanner_inline_state_start(count($dfa->move[$s2]) == 0 && isset($tunnel[$s2]) && isset($dfa->final[$s2]), isset($bt[$s2]) ? $bt[$s2] : null, isset($dfa->ctx[$s2]), isset($dfa->ctx[$s1]));
					emit_scanner_state($f, $dfa, $s2, $dfa->move[$s2], $states, $tunnel, $bt);
				} else {
					$f->scanner_state_transition($s1, $s2, isset($dfa->ctx[$s2]), isset($dfa->ctx[$s1]));
				}
			} else {
				if (isset($dfa->final[$s2])) {
					$f->scanner_state_accept($dfa->final[$s2]->name, isset($dfa->ctx[$s2]));
				} else {
					error("???");
				}
			}
			$f->scanner_state_alt_end($use_switch);
		}
		if (isset($tunnel[$s1])) {
			if (isset($dfa->final[$s1])) {
				$f->scanner_state_else_tunnel($tunnel[$s1], $use_switch, $dfa->final[$s1]->name);
			} else {
				$f->scanner_state_else_tunnel($tunnel[$s1], $use_switch, null);
			}
		} else if (isset($dfa->final[$s1])) {
			if ($dfa->final[$s1] === false) {
				$f->scanner_state_else_accept(null, $use_switch);
			} else {
				$f->scanner_state_else_accept($dfa->final[$s1]->name, $use_switch);
			}
		} else {
			$f->scanner_state_else_error($dfa->n, $use_switch);
	   	}
	}
}

function emit_scanner($f, $func, $dfa) {
	$need_ret = false;
	$states = array();
	if (SCANNER_INLINE) {
		foreach ($dfa->move as $s1 => $v) {
			foreach ($v as $s2 => $s) {
				if (isset($states[$s2])) {
					$states[$s2]++;
				} else {
					$states[$s2] = 1;
				}
			}
		}
	}

	$tunnel = array();
	$tunnel_to = array();
	if (SCANNER_TUNNELS) {
		foreach ($dfa->move as $s1 => $v) {
			if (count($v) > 1) {
				if (isset($dfa->final[$s1])) {
					$final = $dfa->final[$s1];
				} else {
					$final = null;
				}
				$may_tunnel = array();
				foreach ($v as $s2 => $s) {
					if ($s1 !== $s2 && isset($dfa->move[$s2]) &&
					    isset($dfa->move[$s2][$s2]) &&
					    (($final === null && !isset($dfa->final[$s2])) ||
					     (isset($dfa->final[$s2]) && $final === $dfa->final[$s2]))) {
						$may_tunnel[] = $s2;
					}
				}
				$target = null;
				foreach ($may_tunnel as $s3) {
					$target = $s3;
					$set = array();
					foreach ($dfa->move[$s3] as $s4 => $z) {
						if (isset($v[$s4])) {
							foreach ($z as $ch) {
								if (!in_array($ch, $v[$s4])) {
									$set[$ch] = 1;
								}
							}
						} else {
							$target = null;
							continue;
						}
					}
					if ($target != null) {
						// Check that all sumbols from the $set are handled
						if (count($set) == 0) {
							break;
						}
						foreach ($v as $s2 => $s) {
							if (!isset($dfa->move[$target][$s2])) {
								foreach ($s as $ch) {
									unset($set[$ch]);
								}
							}
						}
						if (count($set) == 0) {
							break;
						} else {
							$target = null;
						}
					}
				}
				if ($target !== null) {
//echo "1. $s1 - $target\n";
					foreach ($dfa->move[$target] as $s4 => $z) {
						unset($dfa->move[$s1][$s4]);
						if (SCANNER_INLINE) {
							$states[$s4]--;
						}
					}
					unset($dfa->final[$s1]);
					$tunnel[$s1] = $target;
					$tunnel_to[$target] = true;
				}
			}
		}
		/* combine complex exit paths */
		foreach ($tunnel_to as $target => $dummy) {
			foreach ($dfa->final as $s1 => $dummy) {
				if ($target != $s1 && isset($dfa->move[$s1]) &&
					isset($dfa->final[$s1]) && $dfa->final[$s1] !== false &&
//					 $dfa->final[$s1] === $dfa->final[$target] &&
				    count($dfa->move[$target]) <= count($dfa->move[$s1])) {
					$ok = true;
					$set = array();
					foreach ($dfa->move[$target] as $s2 => $s) {
						if (!isset($dfa->move[$s1][$s2])) {
							$ok = false;
							break;
						} else {
							foreach ($s as $ch) {
								if (!in_array($ch, $dfa->move[$s1][$s2])) {
									$set[$ch] = 1;
								}
							}
						}						    
					}
					if ($ok && count($set) > 0) {
						foreach ($dfa->move[$s1] as $s2 => $s) {
							if (!isset($dfa->move[$target][$s2])) {
								foreach ($s as $ch) {
									unset($set[$ch]);
								}
							}
						}
						if (count($set) > 0) {
							$ok = false;
						}
					}
					if ($ok) {
//echo "2. $s1 - $target\n";
						foreach ($dfa->move[$target] as $s2 => $z) {
							unset($dfa->move[$s1][$s2]);
							if (SCANNER_INLINE) {
								$states[$s2]--;
							}
						}						
						if ($tunnel_to[$target] === true) {
							$need_ret = true;
							if (SCANNER_INLINE) {
								foreach ($dfa->move[$target] as $s2 => $z) {
									$states[$s2]++;
								}
							}
							$s2 = $dfa->n++;
							$dfa->move[$s2] = $dfa->move[$target];
							$dfa->final[$s2] = false;
							$tunnel_to[$target] = $s2;
							$tunnel_to[$s2] = true;
							if (SCANNER_INLINE) {
								$states[$s2] = 0;
							}
						}
						if (SCANNER_INLINE) {
							$states[$tunnel_to[$target]]++;
						}
						$tunnel[$s1] = $tunnel_to[$target];
					}
				}
			}
		}
	}

	$bt = scanner_check_backtracking($dfa, $tunnel);

	$f->scanner_start($func, $need_ret, count($bt) > 0, count($dfa->ctx) > 0);
	$f->scanner_loop_start();
	foreach ($dfa->move as $s1 => $v) {
		if ($s1 == 0 || !SCANNER_INLINE || $states[$s1] > 1 || isset($tunnel_to[$s1])) {
			$f->scanner_state_start($s1, $s1 == 0 || (count($v) == 0 && isset($tunnel[$s1]) && isset($dfa->final[$s1])), !SCANNER_INLINE || isset($states[$s1]), isset($tunnel_to[$s1]), isset($bt[$s1]) ? $bt[$s1] : null);
			emit_scanner_state($f, $dfa, $s1, $v, $states, $tunnel, $bt);
			$f->scanner_state_end();
		}
	}
	$f->scanner_error_state($dfa->n, count($bt) > 0);
	$f->scanner_loop_end();
	$f->scanner_end($func);
}

function emit_la_dfa($f, $grammar, $start, $scanner, $in_pred=false) {
	$f->save_pos();
	$f->la_loop_start($start);
	$dfa = $grammar->la_dfa[$start];

	$used = array();
	foreach ($dfa->move as $state => $move) {
		foreach ($move as $target => $set) {
			if (!isset($dst_use_count[$target])) {
				$used[$target] = 1;
			} else {
				$used[$target]++;
			}
		}
	}

	foreach ($dfa->move as $state => $move) {
		if (!$f::USE_GOTO || !empty($used[$state])) {
			$f->la_state_start($start, $state);
		}
		$first = true;
		foreach ($move as $target => $set) {
			$f->la_state_condition($first, $set);
			$first = false;
			if (isset($dfa->final[$target]) && !isset($dfa->move[$target])) {
				$f->la_state_accept($start, $dfa->final[$target]);
			} else {
				// TODO: Proper scanner
				$f->la_state_transition($start, $state, $target, $scanner->get_sym);
			}
		}
		$f->la_state_else_error($in_pred);
		$f->la_state_end();
	}
	// TODO: Proper scanner
	$f->la_loop_end($start, $scanner->get_sym);
	$f->restore_pos();
}

function emit_parser_code($f, $grammar, $nt, $p, $checked, $scanner, $in_pred = false) {
	while ($p != null) {
		if ($p instanceof Terminal) {
			if (!isset($checked[$p->name])) {
				$f->parser_expect($p->name, $in_pred);
			}
			if (!IGNORE_ACTIONS && $p instanceof RegExp && $p->code !== null) {
				if (!$in_pred) {
					$f->write_code($p->code);
				}
			}
			// FIXME: select proper sub-scanner more careful
			if ($p->next !== null &&
			    $p->next instanceof NonTerminal &&
			    $grammar->nonterm[$p->next->name]->lexer !== null) {
				$scan = $grammar->nonterm[$p->next->name]->lexer;
			} else {
				$scan = $scanner;
			}
			$f->parser_get_sym(
				isset($scanner->skip[$p->name]) ? $scan->func : $scan->get_sym);
		} else if ($p instanceof NonTerminal) {
			$f->parser_nterm($p->name, IGNORE_ATTRIBUTES ? null : $p->attrs,
				isset($scanner->skip[$p->name]), $in_pred);
		} else if ($p instanceof Alternative) {
			if (isset($p->state) && isset($grammar->la_dfa[$p->state])) {
				$use_dfa = true;
				if (!isset($grammar->la_dfa[$p->state]->emited)) {
				    $grammar->la_dfa[$p->state]->emited = true;
				    emit_la_dfa($f, $grammar, $p->state, $scanner, $in_pred);
				}
			} else {
				$use_dfa = false;
			}
			// calculate number of alternatives
			$n = 0;
			$q = $p;
			while ($q != null) {
				$n++;
				$q = $q->alt;
			}
			$first = true;
			$use_switch = ($n - $p->has_pred > $f::IF_VS_CASE);
			if ($p->has_pred) {
				// generate "if" statement for alternatives with predicates
				$q = $p;
				while ($q != null) {
					if ($q->start instanceof Predicate) {
						$set = comp_expected($grammar, $q->start, $nt);
						$f->parser_if_condition($set, $q->start, $first);
						emit_parser_code($f, $grammar, $nt, $q->start, $set, $scanner, $in_pred);
						$first = false;
					}
					$q = $q->alt;
				}
				if ($use_switch) {
					$f->parser_else();
//???					$f->inc_indent($indent);
				}
			}
			if ($use_switch) {
				if ($use_dfa) {
					$f->parser_alt_switch($p->state);
				} else {
					$f->parser_switch();
				}
			}
			$q = $p;
			while ($q != null) {
				if (!($q->start instanceof Predicate)) {
					$set = comp_expected($grammar, $q->start, $nt);
					if ($use_dfa) {
						if ($use_switch) {
							$f->parser_start_alt_case($p->state, $q->start);
						} else {
							$f->parser_alt_if_condition($p->state, $q->start, $first);
							$first = false;
						}
					} else {
						if ($use_switch) {
							$f->parser_start_case($set);
						} else {
							$f->parser_if_condition($set, null, $first);
							$first = false;
						}
					}
					emit_parser_code($f, $grammar, $nt, $q->start, $set, $scanner, $in_pred);
					if ($use_switch) {
						$f->parser_end_case();
					}
				}
				$q = $q->alt;
			}
			if ($use_switch) {
				$f->parser_unexpected_case($in_pred);
				if ($p->has_pred) {
//???					$f->dec_indent($indent);
					$f->parser_end_if();
				}
			} else {
				$f->parser_unexpected($in_pred);
			}
		} else if ($p instanceof Option) {
			$set = comp_first_set($grammar, $p->start);
			if (isset($p->state) && isset($grammar->la_dfa[$p->state])) {
				if (!isset($grammar->la_dfa[$p->state]->emited)) {
				    $grammar->la_dfa[$p->state]->emited = true;
				    emit_la_dfa($f, $grammar, $p->state, $scanner, $in_pred);
				}
				$f->parser_alt_if_condition($p->state, $p->start);
			} else {
				$f->parser_if_condition($set,
					$p->start instanceof Predicate ? $p->start : null);
			}
			emit_parser_code($f, $grammar, $nt, $p->start, $set, $scanner, $in_pred);
			$f->parser_end_if();
		} else if ($p instanceof Iteration) {
			if ($p->min_count == 0) {
				$set = comp_first_set($grammar, $p->start);
				if (isset($p->state) && isset($grammar->la_dfa[$p->state])) {
					$f->parser_start_while();
					if (!isset($grammar->la_dfa[$p->state]->emited)) {
					    $grammar->la_dfa[$p->state]->emited = true;
					    emit_la_dfa($f, $grammar, $p->state, $scanner, $in_pred);
					}
					$f->parser_alt_while_condition($p->state, $p->start);
				} else {
					$f->parser_while_condition($set,
						$p->start instanceof Predicate ? $p->start : null);
				}
				emit_parser_code($f, $grammar, $nt, $p->start, $set, $scanner, $in_pred);
				$f->parser_end_while();
			} else {
				if ($p->start instanceof Terminal) {
					// Optimization to remove check on each iteration
					if (!isset($checked[$p->start->name])) {
						$f->parser_expect($p->start->name, $in_pred);
					}
					$f->parser_do_until();
					emit_parser_code($f, $grammar, $nt, $p->start, array($p->start->name=>1), $scanner, $in_pred);
				} else {
					$f->parser_do_until();
					emit_parser_code($f, $grammar, $nt, $p->start, array(), $scanner, $in_pred);
				}
				$set = comp_first_set($grammar, $p->start);
				if (isset($p->last)) {
					reset($p->last);
					$state = key($p->last);
				}
				if (isset($p->last) && isset($grammar->la_dfa[$state])) {
					if (!isset($grammar->la_dfa[$state]->emited)) {
					    $grammar->la_dfa[$state]->emited = true;
					    emit_la_dfa($f, $grammar, $state, $scanner, $in_pred);
					}
					$f->parser_alt_until_condition($p->state, $p->start);
				} else {
					$f->parser_until_condition($set,
						$p->start instanceof Predicate ? $p->start : null);
				}
			}
		} else if (!IGNORE_ACTIONS && $p instanceof Action) {
			if (!$in_pred) {
				$f->write_code($p->code);
			}
		}
		$p = $p->up ? null : $p->next;
	}
}

function emit_parser_func($f, $grammar, $func, $nt, $scan, $first = false, $check_only = false) {
	$f->parser_func_start($func, $first, (IGNORE_ATTRIBUTES || $check_only) ? null : $nt->attrs);
	
	if (isset($nt->la_states) && count($nt->la_states) > 0) {
		$f->la_func();
		foreach($nt->la_states as $state => $dummy) {
			$f->la_var($state);
		}
	}

	if (!IGNORE_ACTIONS && !$check_only) {
		if (!empty($nt->code)) {
			$f->write_code($nt->code);
		}
	}
	if ($first) {
		$f->parser_get_sym($scan->func);
	}
	emit_parser_code($f, $grammar, $nt->name, $nt->ast, array(), $scan, $check_only);
	$f->parser_func_end($func);
}

function emit_code($grammar) {
	if ($grammar->language == "php") {
	  	$f = new PhpEmitter($grammar->output, $grammar->indent, $grammar->prefix, $grammar->global_vars, $grammar->lineno);
	} else if ($grammar->language == "c") {
	  	$f = new CEmitter($grammar->output, $grammar->indent, $grammar->prefix, $grammar->global_vars, $grammar->lineno);
	} else {
	  	$f = new PhpEmitter($grammar->output, $grammar->indent, $grammar->prefix, $grammar->global_vars, $grammar->lineno);
	}
	if (!EMIT_PARSER_ONLY && !EMIT_SCANNER_ONLY) {
	  	if ($grammar->prologue !== null) {
			$f->write($grammar->prologue);
	  	}

		$f->prologue($grammar);

		if ($f::NEED_FORWARDS) {
			$f->parser_forward_start();
			foreach ($grammar->scanners as $scan) {
		  		if ($scan->skip !== null) {
		  			foreach ($scan->skip as $name => $nt) {
			  			if ($name === "SKIP") {
		  					$f->parser_forward_func("get_sym", true, null);
	  					} else {
		  					$f->parser_forward_func("skip_$name", false, null);
						}
		  			}
	  			}
			}
		  	foreach ($grammar->nonterm as $name => $nt) {
				if ($nt->flags & IS_USED_IN_PRED) {
					$f->parser_forward_func("check_$name", false, null);
				}
			}
			foreach ($grammar->nonterm as $name => $nt) {
		  		if ($nt->flags & IS_USED) {
					$f->parser_forward_func("parse_$name", false, IGNORE_ATTRIBUTES ? null : $nt->attrs);
		  		}
			}		
			foreach ($grammar->pred as $pred) {
				$f->parser_forward_synpred($pred->name);
			}
			$f->parser_forward_end();
		}
	}
  	if (!EMIT_PARSER_ONLY) {
		foreach ($grammar->scanners as $scan) {
			emit_scanner($f, $scan->func, $scan->dfa);
	  		if ($scan->skip !== null) {
	  			foreach ($scan->skip as $name => $nt) {
		  			if ($name === "SKIP") {
		  				emit_parser_func($f, $grammar, "get_sym", $grammar->nonterm[$name], $scan, true);
	  				} else {
		  				emit_parser_func($f, $grammar, "skip_$name", $grammar->nonterm[$name], $scan);
					}
	  			}
	  		}
		}
	}
  	if (!EMIT_SCANNER_ONLY) {
	  	foreach ($grammar->nonterm as $name => $nt) {
	  		if ($nt->flags & IS_USED_IN_PRED) {
				$scan = $nt->use_lexer;
		  		if (!isset($scan->skip[$name])) {
					emit_parser_func($f, $grammar, "check_$name", $nt, $scan, false, true);
		  		}
			}
		}
		if (isset($grammar->la_dfa) && is_array($grammar->la_dfa)) {
			foreach ($grammar->la_dfa as &$dfa) {
				unset($dfa->emited);
			}
		}
	  	foreach ($grammar->pred as $pred) {
	  		// TODO: scanner ???
			if (!$pred->start instanceof NonTerminal ||
			    $pred->start->next != null) {
				$f->parser_synpred_start($pred);
				emit_parser_code($f, $grammar, $pred->name, $pred->start, array(), $scan, 1);
				$f->parser_synpred_end($pred);
			}
			$f->parser_synpred($pred);
	  	}
	  	foreach ($grammar->nonterm as $name => $nt) {
	  		if ($nt->flags & IS_USED) {
				$scan = $nt->use_lexer;
		  		if (!isset($scan->skip[$name])) {
					emit_parser_func($f, $grammar, "parse_$name", $nt, $scan);
		  		}
			}
	  	}

		$f->main("parse", $grammar->start, IGNORE_ATTRIBUTES ? null : $grammar->nonterm[$grammar->start]->attrs);
	}

  	if (!EMIT_PARSER_ONLY && !EMIT_SCANNER_ONLY) {
	  	if ($grammar->epilogue !== null) {
			$f->write($grammar->epilogue);
	  	}
	}

  	$f->close();
}


/*****/

function getmicrotime()
{
  $t = gettimeofday();
  return ($t['sec'] + $t['usec'] / 1000000);
}

function start_test()
{
	ob_start();
	return getmicrotime();
}

function end_test($start, $name)
{
	global $total;
	$end = getmicrotime();
	ob_end_clean();
	$total += $end-$start;
	$num = number_format($end-$start,3);
	$pad = str_repeat(" ", 32-strlen($name)-strlen($num));

	echo $name.$pad.$num."\n";
	ob_start();
	return getmicrotime();
}

function total()
{
  global $total;
  $pad = str_repeat("-", 32);
  echo $pad."\n";
  $num = number_format($total,3);
  $pad = str_repeat(" ", 32-strlen("Total")-strlen($num));
  echo "Total".$pad.$num."\n";
}

/*****/
// main
$fn = null;
for ($i = 1; $i < $argc; $i++) {
	if ($argv[$i][0] === "-") {
		if ($argv[$i] == "--dump-nfa") {
			define("DUMP_NFA", true);
		} else if ($argv[$i] == "--dump-nfa-dfa") {
			define("DUMP_NFA_DFA", true);
		} else if ($argv[$i] == "--dump-dfa") {
			define("DUMP_DFA", true);
		} else if ($argv[$i] == "--dump-min-dfa") {
			define("DUMP_MIN_DFA", true);
		} else if ($argv[$i] == "--dump-la-nfa") {
			define("DUMP_LA_NFA", true);
		} else if ($argv[$i] == "--dump-la-nfa-dfa") {
			define("DUMP_LA_NFA_DFA", true);
		} else if ($argv[$i] == "--dump-la-dfa") {
			define("DUMP_LA_DFA", true);
		} else if ($argv[$i] == "--dump-la-min-dfa") {
			define("DUMP_LA_MIN_DFA", true);
		} else if ($argv[$i] == "--dump-dot-grammar") {
			define("DUMP_DOT_GRAMMAR", true);
		} else if ($argv[$i] == "--dump-dot-ast") {
			define("DUMP_DOT_AST", true);
		} else if ($argv[$i] == "--dump-dot-la-nfa") {
			define("DUMP_DOT_LA_NFA", true);
		} else if ($argv[$i] == "--dump-dot-la-dfa") {
			define("DUMP_DOT_LA_DFA", true);
		} else if ($argv[$i] == "--dump-dot-la-min-dfa") {
			define("DUMP_DOT_LA_MIN_DFA", true);
		} else if ($argv[$i] == "--dump-first-follow") {
			define("DUMP_FIRST_FOLLOW", true);
		} else if ($argv[$i] == "--alt-dfa") {
			define("ALT_DFA", true);
		} else if ($argv[$i] == "--ll1") {
			define("LL1", true);
		} else if ($argv[$i] == "--emit-scanner-only") {
			define("EMIT_SCANNER_ONLY", true);
		} else if ($argv[$i] == "--emit-parser-only") {
			define("EMIT_PARSER_ONLY", true);
		} else if ($argv[$i] == "--ignore-actions") {
			define("IGNORE_ACTIONS", true);
		} else if ($argv[$i] == "--ignore-attributes") {
			define("IGNORE_ATTRIBUTES", true);
		} else if ($argv[$i] == "-t") {
			define("PROFILE", true);
		} else if ($argv[$i] == "-c") {
			define("EMIT_TO_STDOUT", true);
		} else if ($argv[$i] == "-v") {
			define("VERBOSE", true);
		} else if ($argv[$i] == "--run") {
			define("TEST_RUN", true);
		}
	} else {
		$fn = $argv[$i];
	}
}
if ($fn === null) {
	die("Invalid arguments\n");
}
if (!defined("DUMP_NFA")) {
	define("DUMP_NFA", false);
}
if (!defined("DUMP_NFA_DFA")) {
	define("DUMP_NFA_DFA", false);
}
if (!defined("DUMP_DFA")) {
	define("DUMP_DFA", false);
}
if (!defined("DUMP_MIN_DFA")) {
	define("DUMP_MIN_DFA", false);
}
if (!defined("DUMP_LA_NFA")) {
	define("DUMP_LA_NFA", false);
}
if (!defined("DUMP_LA_NFA_DFA")) {
	define("DUMP_LA_NFA_DFA", false);
}
if (!defined("DUMP_LA_DFA")) {
	define("DUMP_LA_DFA", false);
}
if (!defined("DUMP_LA_MIN_DFA")) {
	define("DUMP_LA_MIN_DFA", false);
}
if (!defined("DUMP_DOT_GRAMMAR")) {
	define("DUMP_DOT_GRAMMAR", false);
}
if (!defined("DUMP_DOT_AST")) {
	define("DUMP_DOT_AST", false);
}
if (!defined("DUMP_DOT_LA_NFA")) {
	define("DUMP_DOT_LA_NFA", false);
}
if (!defined("DUMP_DOT_LA_DFA")) {
	define("DUMP_DOT_LA_DFA", false);
}
if (!defined("DUMP_DOT_LA_MIN_DFA")) {
	define("DUMP_DOT_LA_MIN_DFA", false);
}
if (!defined("DUMP_FIRST_FOLLOW")) {
	define("DUMP_FIRST_FOLLOW", false);
}
if (!defined("PROFILE")) {
	define("PROFILE", false);
}
if (!defined("ALT_DFA")) {
	define("ALT_DFA", false);
}
if (!defined("LL1")) {
	define("LL1", false);
}
if (!defined("EMIT_SCANNER_ONLY")) {
	define("EMIT_SCANNER_ONLY", false);
}
if (!defined("EMIT_PARSER_ONLY")) {
	define("EMIT_PARSER_ONLY", false);
}
if (!defined("EMIT_TO_STDOUT")) {
	define("EMIT_TO_STDOUT", false);
}
if (!defined("IGNORE_ACTIONS")) {
	define("IGNORE_ACTIONS", false);
}
if (!defined("IGNORE_ATTRIBUTES")) {
	define("IGNORE_ATTRIBUTES", false);
}
if (!defined("VERBOSE")) {
	define("VERBOSE", false);
}
if (!defined("TEST_RUN")) {
	define("TEST_RUN", false);
}
if (PROFILE) date_default_timezone_set("GMT");
if (PROFILE) $t0 = $t = start_test();
$buf  = file_get_contents($fn);
if ($buf === false) error("file not found");
$len  = strlen($buf);
$buf .= "\000";
$grammar = new Grammar();
$grammar->term["<EOF>"] = new TermDef("EOF", 0, true);
parse($grammar);
if (PROFILE) $t = end_test($t, "parse");
$ok = true;
$ok &= test_undefined_nterm($grammar);
if (PROFILE) $t = end_test($t, "test_undefined");
$ok &= test_unused_nterm($grammar);
if (PROFILE) $t = end_test($t, "test_unused");
if ($ok) {
	$ok &= test_underivable_nterms($grammar);
	if (PROFILE) $t = end_test($t, "test_underivable");
	test_nullable_nterms($grammar);
	if (PROFILE) $t = end_test($t, "test_nullable");
	$ok &= test_circular_nterms($grammar);
	if (PROFILE) $t = end_test($t, "test_circular");
	if ($ok) {
		$ok &= test_ambiguity($grammar);
		if (PROFILE) $t = end_test($t, "test_ambiguity");
		if ($ok) {
			$ok &= test_left_recursion($grammar);
			if (PROFILE) $t = end_test($t, "test_left_recursion");
			if ($ok) {
				if (DUMP_DOT_GRAMMAR) {
					grammar_to_dots($grammar, DOT_DUMP_GRAMMAR);
				}
				if (DUMP_DOT_AST) {
					grammar_to_dots($grammar, DOT_DUMP_AST);
				}
				comp_first_sets($grammar);
				if (PROFILE) $t = end_test($t, "comp_first_sets");
				comp_follow_sets($grammar);
				if (PROFILE) $t = end_test($t, "comp_follow_sets");
				if (DUMP_FIRST_FOLLOW) {
					dump_first_follow($grammar);
				}
				test_ll1($grammar);
				if (PROFILE) $t = end_test($t, "test_ll1");

				foreach ($grammar->nonterm as $nterm => $nt) {
					if ($nt->lexer !== null) {
						$scan = $nt->lexer;
						$scan->follow = $nt->follow;
						$nfa = build_nfa($grammar, $nterm, $scan);
						$grammar->scanners[$nterm] = $scan;
						if (DUMP_NFA) {
							dump_fa($grammar, "NFA $nterm", $nfa);
						}
						if (PROFILE) $t = end_test($t, "build_nfa $nterm");

						if (ALT_DFA) {
							$scan->dfa = build_alt_dfa($nfa);
						} else {
							$scan->dfa = build_dfa($nfa);
						}
						if ($scan->dfa !== null) {
							if (DUMP_DFA) {
								dump_fa($grammar, "DFA $nterm", $scan->dfa);
							}
							if (PROFILE) $t = end_test($t, "build_dfa $nterm");

							minimize_dfa($scan->dfa);
							if (DUMP_MIN_DFA) {
								dump_fa($grammar, "mDFA $nterm", $scan->dfa);
							}
							if (PROFILE) $t = end_test($t, "minimize_dfa $nterm");
						} else {
							$ok = false;
						}
					}
				}

				if ($ok) {
					emit_code($grammar);
					if (PROFILE) $t = end_test($t, "emit_code");
				}
			}
		}
	}
}
if (PROFILE) total($t0, "Total");

if (TEST_RUN && !EMIT_TO_STDOUT) {
	if ($grammar->language == "php" && !empty($grammar->output)) {
		passthru("php " . $grammar->output);
	}
}
