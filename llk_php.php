<?php
class PhpEmitter extends Emitter {
	const IF_VS_CASE    = 4;
	const IF_VS_SET     = 4;
	const USE_GOTO      = false;
	const NEED_FORWARDS = false;
	const COMBINE_FINAL = false;

	function __construct($fn, $indent, $prefix, $global_vars, $lineno) {
		parent::__construct($fn, $indent, $prefix, $global_vars, $lineno);
		$this->write("<?php\n");
	}

	function prologue($grammar) {
		$this->grammar = $grammar;

		foreach ($grammar->term as $term) {
			$this->write("const $term->const_name = $term->val;\n");
		}
		$this->write("\n");


		$this->write("\$sym_name = [\n");
		foreach ($grammar->term as $term) {
			if ($term->special) {
				$this->write("\t\"<$term->name>\",\n");
			} else {
				$this->write("\t\"$term->name\",\n");
			}
		}
		$this->write("];\n\n");

		$this->write("\$buf = \"\";\n");
		$this->write("\$len = 0;\n");
		$this->write("\$pos = 0;\n");
		$this->write("\$text = 0;\n");
		if ($this->lineno) {
			$this->write("\$line = 1;\n");
		}
		$this->write("\n");

		$this->write("function escape_char(\$c) {\n");
		$this->write("\tif (ord(\$c) == ord('\'')) {\n");
		$this->write("\t\treturn \"\\'\";\n");
		$this->write("\t} else if (ord(\$c) == ord('\\\\')) {\n");
		$this->write("\t\treturn \"\\\\\";\n");
		$this->write("\t} else if (ord(\$c) == ord(\"\\r\")) {\n");
		$this->write("\t\treturn \"\\\\r\";\n");
		$this->write("\t} else if (ord(\$c) == ord(\"\\n\")) {\n");
		$this->write("\t\treturn \"\\\\n\";\n");
		$this->write("\t} else if (ord(\$c) == ord(\"\\t\")) {\n");
		$this->write("\t\treturn \"\\\\t\";\n");
		$this->write("\t} else if (ord(\$c) == ord(\"\\v\")) {\n");
		$this->write("\t\treturn \"\\\\v\";\n");
		$this->write("\t} else if (ord(\$c) == ord(\"\\f\")) {\n");
		$this->write("\t\treturn \"\\\\f\";\n");
		$this->write("\t} else if (ord(\$c) < ord(' ') || ord(\$c) >= 127) {\n");
		$this->write("\t\t\$c = ord(\$c);\n");
		$this->write("\t\treturn \"\\\\\" . chr(ord('0') + ((\$c >> 6) % 8)) .\n");
		$this->write("\t\t\tchr(ord('0') + ((\$c >> 3) % 8)) .\n");
		$this->write("\t\t\tchr(ord('0') + (\$c % 8));\n");
		$this->write("\t} else {\n");
		$this->write("\t\treturn \$c;\n");
		$this->write("\t}\n");
		$this->write("}\n");
		$this->write("\n");
		$this->write("function escape_string(\$s) {\n");
		$this->write("\t\$n = strlen(\$s);\n");
		$this->write("\t\$r = \"\";\n");
		$this->write("\tfor (\$i = 0; \$i < \$n; \$i++) {\n");
		$this->write("\t\t\$r .= escape_char(\$s[\$i]);\n");
		$this->write("\t}\n");
		$this->write("\treturn \$r;\n");
		$this->write("}\n");
		$this->write("\n");
		$this->write("function get_text(\$f = 0, \$l = 0) {\n");
		$this->write("\tglobal \$buf, \$text, \$pos;\n");
		$this->write("\t\$text_len = \$pos - \$text;\n");
		$this->write("\treturn (\$text_len - \$f - \$l > 0) ? substr(\$buf, \$text + \$f, \$text_len - \$f - \$l) : null;\n");
		$this->write("}\n");
		$this->write("\n");
	}

	function gen_escape_char($c) {
		if ($c === '"') {
			return "\\\"";
		} else if ($c === "\\") {
			return "\\\\";
		} else if ($c < ' ' || ord($c) >= 127) {
			if ($c === "\r") {
				return "\\r";
			} else if ($c === "\n") {
				return "\\n";
			} else if ($c === "\t") {
				return "\\t";
			} else if ($c === "\v") {
				return "\\v";
			} else if ($c === "\f") {
				return "\\f";
			} else {
				$c = ord($c);
				return "\\" . chr(ord('0') + (($c >> 6) % 8)) .
					chr(ord('0') + (($c >> 3) % 8)) .
					chr(ord('0') + ($c % 8));
			}
		} else {
			return $c;
		}
	}

	function gen_charset_condition($s) {
		if (count($s) == 256) { // ANY
			return "\$pos < \$len";
		} else if (count($s) == 1) {
			if ($s[0] === "<EOF>") {
				return "\$pos >= \$len";
			} else {
				$c1 = $this->gen_escape_char($s[0]);
				return "\$ch === \"$c1\"";
			}
		} else {
			sort($s);
			$n = count($s);
			$i = 0;
			$m = 0;
			$r = "";
			$r2 = "";
			$first = true;
			while ($i < $n) {
				$j = $i;
				$c1 = $s[$i];
				$k = ord($c1);
				$i++;
				while ($i < $n && ord($s[$i]) == $k + 1) {
					$c2 = $s[$i];
					$k++;
					$i++;
				}
				if ($first) {
					$first = false;
				} else {
					$r .= " || ";
				}
				if ($i - $j == 1) {
					$c1 = $this->gen_escape_char($c1);
					$r .= "\$ch === \"$c1\"";
					$m += 1;
				} else if ($i - $j == 2) {
					$c1 = $this->gen_escape_char($c1);
					$c2 = $this->gen_escape_char($c2);
					$r .= "\$ch === \"$c1\" || \$ch === \"$c2\"";
					$m += 2;
				} else {
					if (ord($c1) == 0) {
						$c2 = $this->gen_escape_char($c2);
						$r .= "\$pos < \$len && (\$ch <= \"$c2\"";
						$r2 = ")";
						$m += 2;
					} else if (ord($c2) == 255) {
						$c1 = $this->gen_escape_char($c1);
						$r .= "\$ch >= \"$c1\"";
						$m += 1;
					} else {
						$c1 = $this->gen_escape_char($c1);
						$c2 = $this->gen_escape_char($c2);
						$r .= "(\$ch >= \"$c1\" && \$ch <= \"$c2\")";
						$m += 2;
					}
				}
			}
			return $r . $r2;
		}
	}

	function gen_neg_charset_condition($s) {
		if (count($s) == 256) { // ANY
			return "\$pos >= \$len";
		} else if (count($s) == 1) {
			if ($s[0] === "<EOF>") {
				return "\$pos < \$len";
			} else {
				$c1 = $this->gen_escape_char($s[0]);
				return "\$ch !== \"$c1\"";
			}
		} else {
			sort($s);
			$n = count($s);
			$i = 0;
			$m = 0;
			$r = "";
			$first = true;
			while ($i < $n) {
				$j = $i;
				$c1 = $s[$i];
				$k = ord($c1);
				$i++;
				while ($i < $n && ord($s[$i]) == $k + 1) {
					$c2 = $s[$i];
					$k++;
					$i++;
				}
				if ($first) {
					$first = false;
				} else {
					$r .= " && ";
				}
				if ($i - $j == 1) {
					$c1 = $this->gen_escape_char($c1);
					$r .= "\$ch !== \"$c1\"";
					$m += 1;
				} else if ($i - $j == 2) {
					$c1 = $this->gen_escape_char($c1);
					$c2 = $this->gen_escape_char($c2);
					$r .= "\$ch !== \"$c1\" && \$ch !== \"$c2\"";
					$m += 2;
				} else {
					if (ord($c1) == 0) {
						$c2 = $this->gen_escape_char($c2);
						$r .= "\$ch > \"$c2\"";
						$m += 1;
					} else if (ord($c2) == 255) {
						$c1 = $this->gen_escape_char($c1);
						$r .= "\$ch < \"$c1\"";
						$m += 1;
					} else {
						$c1 = $this->gen_escape_char($c1);
						$c2 = $this->gen_escape_char($c2);
						$r .= "(\$ch < \"$c1\" || \$ch > \"$c2\")";
						$m += 2;
					}
				}
			}
			return $r;
		}
	}

	function scanner_start($func, $need_ret, $need_backtracking, $ctx) {
		$this->indent();
		$this->write("function $func() {\n");
		$this->inc_indent();
		$this->indent();
		$this->write("global \$buf, \$pos, \$len, \$text;\n");
		if ($this->lineno) {
			$this->indent();
			$this->write("global \$line;\n");
		}
		$this->write("\n");
		if (self::USE_GOTO) {
			$this->write("_yy_state_start:\n");
		}
		$this->indent();
		$this->write("\$text = \$pos;\n");
		if ($need_backtracking) {
			$this->indent();
			$this->write("\$accept = null;\n");
		}
		if ($ctx) {
			$this->indent();
			$this->write("\$ctx = 0;\n");
		}
	}

	function scanner_loop_start() {
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("\$state = 0;\n");
			$this->indent();
			$this->write("while (1) {\n");
			$this->inc_indent();
			$this->indent();
			$this->write("\$ch = \$buf[\$pos];\n");
			$this->indent();
			$this->write("switch (\$state) {\n");
			$this->inc_indent();
		} else {
			$this->indent();
			$this->write("\$ch = \$buf[\$pos];\n");
		}
	}

	function scanner_state_start($state, $first, $used, $tunnel_to, $sym = null) {
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("case $state:\n");
			$this->inc_indent();
		} else {
			if ($used) {
				$this->write("_yy_state_$state:\n");
			}
			if (!$first) {
				$this->indent();
				$this->write("\$pos++;\n");
				$this->indent();
				$this->write("\$ch = \$buf[\$pos];\n");
			}
			if ($tunnel_to) {
				$this->write("_yy_tunnel_$state:\n");
			}
		}
		if ($sym !== null) {
			$this->indent();
			$this->write("\$accept = " . $this->grammar->term[$sym]->const_name . ";\n");
			$this->indent();
			$this->write("\$accept_pos = \$pos;\n");
		}
	}

	function scanner_state_switch_start() {
		$this->indent();
		$this->write("switch (\$ch) {\n");
		$this->inc_indent();
	}

	function scanner_state_switch_end() {
		$this->dec_indent();
		$this->indent();
		$this->write("}\n");
	}

	function scanner_state_tunnel_accept($state, $sym) {
		$this->indent();
		$this->write("\$ret = " . $this->grammar->term[$sym]->const_name . ";\n");
		$this->indent();
		if (!self::USE_GOTO) {
			$this->write("\$state = $state;\n");
		} else {
			$this->write("goto _yy_state_$state;\n");
		}
	}

	function scanner_state_condition($first, $set, $use_switch, $error_state) {
		if ($use_switch) {
			foreach ($set as $ch) {
				$this->indent();
				if ($ch === "<EOF>") {
					$this->write("case \"\\000\":\n");
					$this->indent(1);
					if (self::USE_GOTO) {
						$this->write("if (\$ch === \"\\000\" && \$pos < \$len) goto _yy_state_error;\n");
					} else {
						$this->write("if (\$ch === \"\\000\" && \$pos < \$len) {\$state = $error_state; break;};\n");
					}
				} else {
					$ch = $this->gen_escape_char($ch);
					$this->write("case \"$ch\":\n");
				}
			}
		} else {
			$if = $first ? "if" : "} else if";
			$this->indent();
			$this->write("$if (" . $this->gen_charset_condition($set) . ") {\n");
		}
		$this->inc_indent();
		if ($this->lineno) {
			if (count($set) == 1 && $set[0] === "\n") {
				$this->indent();
				$this->write("\$line++;\n");
			} else if (in_array("\n", $set, true)) {
				$this->indent();
				$this->write("if (\$ch === \"\\n\") {\n");
				$this->indent(1);
				$this->write("\$line++;\n");
				$this->indent();
				$this->write("}\n");
			}
		}
	}

	function scanner_state_tunnel_condition($set, $state, $sym) {
		$this->indent();
		$this->write("if (" . $this->gen_neg_charset_condition($set) . ") ");
		if ($sym !== null) {
			$this->write("{\$ret = " . $this->grammar->term[$sym]->const_name . "; ");
			if (self::USE_GOTO) {
				$this->write("goto _yy_tunnel_$state;}\n");
			} else {
				$this->write("\$state = $state; break;}\n");
			}		
		} else {
			if (self::USE_GOTO) {
				$this->write("goto _yy_tunnel_$state;\n");
			} else {
				$this->write("{\$state = $state; break;}\n");
			}		
		}
	}

	function scanner_state_alt_end($use_switch) {
		$this->dec_indent();
		if (!self::USE_GOTO && $use_switch) {
			$this->indent(1);
			$this->write("break;\n");
		}
	}

	function scanner_inline_state_start($tunnel, $sym = null, $ctx, $prev_ctx) {
		if ($ctx) {
			$this->indent();
			$this->write("\$ctx++;\n");
		} else if ($prev_ctx) {
			$this->indent();
			$this->write("\$ctx = 0;\n");
		}
		if (!self::USE_GOTO || !$tunnel) {		
			$this->indent();
			$this->write("\$pos++;\n");
			if (!$tunnel) {
				$this->indent();
				$this->write("\$ch = \$buf[\$pos];\n");
			}
			if ($sym !== null) {
				$this->indent();
				$this->write("\$accept = " . $this->grammar->term[$sym]->const_name . ";\n");
				$this->indent();
				$this->write("\$accept_pos = \$pos;\n");
			}
		}
	}

	function scanner_state_transition($state, $target, $ctx, $prev_ctx) {
		if ($ctx) {
			$this->indent();
			$this->write("\$ctx++;\n");
		} else if ($prev_ctx) {
			$this->indent();
			$this->write("\$ctx = 0;\n");
		}
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("\$pos++;\n");
			if ($state != $target) {
				$this->indent();
				$this->write("\$state = $target;\n");
			}
		} else {
			$this->indent();
			$this->write("goto _yy_state_$target;\n");
		}
	}

	function scanner_state_accept($sym, $ctx) {
		if ($ctx) {
			$this->indent();
			$this->write("\$pos -= \$ctx;\n");
		} else {
			$this->indent();
			$this->write("\$pos++;\n");
		}
		if (self::COMBINE_FINAL) {
			$this->indent();
			$this->write("\$ret = " . $this->grammar->term[$sym]->const_name . ";\n");
			$this->indent();
			$this->write("goto _yy_fin;\n");
		} else {
			$this->indent();
			$this->write("return " . $this->grammar->term[$sym]->const_name . ";\n");
		}
	}

	function scanner_state_else_accept($sym, $use_switch) {
		$this->indent();
		if ($use_switch) {
			$this->write("default:\n");
		} else {
			$this->write("} else {\n");
		}
		if (self::COMBINE_FINAL) {
			if ($sym !== null) {
				$this->indent(1);
				$this->write("\$ret = " . $this->grammar->term[$sym]->const_name . ";\n");
			}
			$this->indent(1);
			$this->write("goto _yy_fin;\n");
		} else {
			if ($sym !== null) {
				$this->indent(1);
				$this->write("return " . $this->grammar->term[$sym]->const_name . ";\n");
			} else {
				$this->indent(1);
				$this->write("return \$ret;\n");
			}
		}
		if ($use_switch) {
			$this->dec_indent();
		}
		$this->indent();
		$this->write("}\n");
	}

	function scanner_state_else_error($error_state, $use_switch) {
		$this->indent();
		if ($use_switch) {
			$this->write("default:\n");
		} else {
			$this->write("} else {\n");
		}
		$this->indent(1);
		if (!self::USE_GOTO) {
			$this->write("\$state = $error_state;\n");
		} else {
			$this->write("goto _yy_state_error;\n");
		}
		if ($use_switch) {
			$this->dec_indent();
		}
		$this->indent();
		$this->write("}\n");
	}

	function scanner_state_else_tunnel($tunnel_state, $use_switch, $sym) {
		$this->indent();
		if ($use_switch) {
			$this->write("default:\n");
		} else {
			$this->write("} else {\n");
		}
		if ($sym !== null) {
			$this->indent(1);
			$this->write("\$ret = " . $this->grammar->term[$sym]->const_name . ";\n");
		}
		$this->indent(1);
		if (!self::USE_GOTO) {
			$this->write("\$state = $tunnel_state;\n");
		} else {
			$this->write("goto _yy_tunnel_$tunnel_state;\n");
		}
		if ($use_switch) {
			$this->dec_indent();
		}
		$this->indent();
		$this->write("}\n");
	}

	function scanner_state_end() {
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("break;\n");
			$this->dec_indent();
		}
	}

	function scanner_error_state($error_state, $need_backtracking) {
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("case $error_state:\n");
			$this->inc_indent();
		} else {
			$this->write("_yy_state_error:\n");
		}

		if ($need_backtracking) {
			$this->indent();
			$this->write("if (\$accept !== null) {\n");
			$this->indent(1);
			$this->write("\$pos = \$accept_pos;\n");
			$this->indent(1);
			$this->write("return \$accept;\n");
			$this->indent();
			$this->write("}\n");
		}

		$this->indent();
		$this->write("if (\$pos >= \$len) {\n");
		$this->indent(1);
		$this->write("error(\"Unexpected <EOF>\");\n");
		$this->indent();
		$this->write("} else if (\$pos == \$text) {\n");
		$this->indent(1);
		$this->write("error(\"Unexpected character '\" . escape_char(\$ch) . \"'\");\n");
		$this->indent();
		$this->write("} else {\n");
		$this->indent(1);
		$this->write("error(\"Unexpected sequence '\" . escape_string(substr(\$buf, \$text, 1 + \$pos - \$text)) . \"'\");\n");
		$this->indent();
		$this->write("}\n");
		$this->indent();
		$this->write("\$pos++;\n");
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("\$text = \$pos;\n");
			$this->indent();
			$this->write("\$state = 0;\n");
			$this->indent();
			$this->write("break;\n");
			$this->dec_indent();
		} else {
			$this->indent();
			$this->write("goto _yy_state_start;\n");
		}
	}

	function scanner_loop_end() {
		if (!self::USE_GOTO) {
			$this->dec_indent();
			$this->indent();
			$this->write("}\n");
			$this->dec_indent();
			$this->indent();
			$this->write("}\n");
		} else if (self::COMBINE_FINAL) {
			$this->write("_yy_fin:\n");
			$this->indent(0);
			$this->write("return \$ret;\n");
		}
	}

	function scanner_end($func) {
		$this->dec_indent();
		$this->indent();
		$this->write("}\n");
		$this->write("\n");
	}

	function save_pos() {
		$this->indent();
		$this->write("\$save_pos  = \$pos;\n");
		$this->indent();
		$this->write("\$save_text = \$text;\n");
		if ($this->lineno) {
			$this->indent();
			$this->write("\$save_line = \$line;\n");
		}
	}

	function restore_pos() {
		$this->indent();
		$this->write("\$pos  = \$save_pos;\n");
		$this->indent();
		$this->write("\$text = \$save_text;\n");
		if ($this->lineno) {
			$this->indent();
			$this->write("\$line = \$save_line;\n");
		}
	}

	function gen_condition($set, $neg = false) {
		if (count($set) == 0) {
			return $neg ? "0" : "1";
		} else if (count($set) <= self::IF_VS_SET) {
			foreach($set as $sym => $dummy) {
				if ($neg) {
					if (isset($s)) {
						$s .= " && \$sym != " . $this->grammar->term[$sym]->const_name;
					} else {
						$s = "\$sym != " . $this->grammar->term[$sym]->const_name;
					}
				} else {
					if (isset($s)) {
						$s .= " || \$sym == " . $this->grammar->term[$sym]->const_name;
					} else {
						$s = "\$sym == " . $this->grammar->term[$sym]->const_name;
					}
				}
			}
		} else {
			if ($neg) {
				$s = "!in_array(\$sym, array(";
			} else {
				$s = "in_array(\$sym, array(";
			}
			$first = true;
			foreach($set as $sym => $dummy) {
				if ($first) {
					$first = false;
				} else {
					$s .= ",";
				}
				$s .= $this->grammar->term[$sym]->const_name;
			}
			$s .= '))';
		}
		return $s;
	}

	function collect_states($state, $p, &$set) {
		while ($p != null &&
		       ($p instanceof Epsilon || $p instanceof Action || $p instanceof Predicate)) {
			$p = $p->next;
		}
		while ($p != null) {
			if ($p->state != $state) {
				$set[$p->state] = 1;
				return;
			} else if ($p instanceof Alternative) {
				$q = $p;
				do {
					$this->collect_states($state, $q->start, $set);
					$q = $q->alt;
				} while ($q != null);
			} else if ($p instanceof Option) {
				$this->collect_states($state, $p->start, $set);
			} else if ($p instanceof Iteration) {
				$this->collect_states($state, $p->start, $set);
			}
			$p = $p->up ? null : $p->next;
		}
	}

	function gen_alt_condition($state, $p) {
		$set = array();
		$this->collect_states($state, $p, $set);
		$first = true;
		$s = "";
		foreach ($set as $alt => $dummy) {
			if ($first) {
				$first = false;
			} else {
				$s .= " || ";
			}
			$s  .= "\$alt$state == $alt";
		}
		return $s;
	}

	function gen_alt_neg_condition($state, $p) {
		$set = array();
		$this->collect_states($state, $p, $set);
		$first = true;
		$s = "";
		foreach ($set as $alt => $dummy) {
			if ($first) {
				$first = false;
			} else {
				$s .= " && ";
			}
			$s  .= "\$alt$state != $alt";
		}
		return $s;
	}

	function gen_dfa_condition($set, $neg = false) {
		if (count($set) == 0) {
			return "1";
		} else if (count($set) <= self::IF_VS_SET) {
			foreach($set as $sym) {
				if ($neg) {
					if (isset($s)) {
						$s .= " && \$sym2 != " . $this->grammar->term[$sym]->const_name;
					} else {
						$s = "\$sym2 != " . $this->grammar->term[$sym]->const_name;
					}
				} else {
					if (isset($s)) {
						$s .= " || \$sym2 == " . $this->grammar->term[$sym]->const_name;
					} else {
						$s = "\$sym2 == " . $this->grammar->term[$sym]->const_name;
					}
				}
			}
		} else {
			if ($neg) {
				$s = "!in_array(\$sym2, array(";
			} else {
				$s = "in_array(\$sym2, array(";
			}
			$first = true;
			foreach($set as $sym) {
				if ($first) {
					$first = false;
				} else {
					$s .= ",";
				}
				$s .= $this->grammar->term[$sym]->const_name;
			}
			$s .= '))';
		}
		return $s;
	}

	function la_func() {
		$this->indent();
		$this->write("global \$pos, \$text, \$line;\n");
		$this->write("\n");
	}

	function la_var($state) {
	}

	function la_loop_start($start) {
		$this->indent();
		$this->write("\$alt$start = -2;\n");
		$this->indent();
		$this->write("\$sym2 = \$sym;\n");
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("\$state = 0;\n");
			$this->indent();
			$this->write("while (1) {\n");
			$this->inc_indent();
			$this->indent();
			$this->write("switch (\$state) {\n");
			$this->inc_indent();
		}
	}

	function la_state_start($start, $state) {
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("case $state:\n");
			$this->inc_indent();
		} else {
			$this->write("_yy_state_$start"."_$state:\n");
		}
	}

	function la_state_condition($first, $set) {
		$if = $first ? "if" : "} else if";
		$this->indent();
		$this->write("$if (". $this->gen_dfa_condition($set) . ") {\n");
		$this->inc_indent();
	}

	function la_state_accept($start, $alt) {
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("\$alt$start = $alt;\n");
			$this->indent();
			$this->write("break 2;\n");
		} else {
			$this->indent();
			$this->write("\$alt$start = $alt;\n");
			$this->indent();
			$this->write("goto _yy_state_$start;\n");
		}
		$this->dec_indent();
	}

	function la_state_transition($start, $state, $target, $get_sym) {
		if (!self::USE_GOTO) {
			if ($state != $target) {
				$this->indent();
				$this->write("\$state = $target;\n");
			}
		} else {
			$this->indent();
			$this->write("\$sym2 = $get_sym();\n");
			$this->indent();
			$this->write("goto _yy_state_$start"."_$target;\n");
		}
		$this->dec_indent();
	}

	function la_state_else_error() {
		$this->indent();
		$this->write("} else {\n");
		$this->indent(1);
		$this->write("error(\"unexpected '{\$GLOBALS['sym_name'][\$sym2]}'\");\n");
		$this->indent();
		$this->write("}\n");
	}

	function la_state_end() {
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("break;\n");
			$this->dec_indent();
		}
	}

	function la_loop_end($start, $get_sym) {
		if (!self::USE_GOTO) {
			$this->dec_indent();
			$this->indent();
			$this->write("}\n");
			$this->indent();
			$this->write("\$sym2 = $get_sym();\n");
			$this->dec_indent();
			$this->indent();
			$this->write("}\n");
		} else {
			$this->write("_yy_state_$start:\n");
		}
	}

	function write_code($code) {
		$this->indent();
		// TODO: format code
		$this->write(trim($code) . "\n");
	}

	function parser_func_start($name, $first, $attrs) {
		$this->indent();
		$this->write("function $name(" . ($first ? "" : "\$sym") . $this->gen_attrs($attrs, $first) . ") {\n");
		$this->inc_indent();
	}

	function parser_func_end($name) {
		$this->indent();
		$this->write("return \$sym;\n");
		$this->dec_indent();
		$this->indent();
		$this->write("}\n\n");
	}

	function parser_get_sym($get_sym) {
		$this->indent();
		$this->write("\$sym = $get_sym();\n");
	}

	function parser_expect($sym, $check_only = false) {
		$this->indent();
		$this->write("if (\$sym != " . $this->grammar->term[$sym]->const_name . ") {\n");
		$this->indent(1);
		if ($check_only) {
			$this->write("return false;\n");
		} else {
			if ($this->grammar->term[$sym]->special) {
				$this->write("error(\"<" . $this->grammar->term[$sym]->name . "> expected, got '{\$GLOBALS['sym_name'][\$sym]}'\");\n");
			} else {
				$this->write("error(\"'$sym' expected, got '{\$GLOBALS['sym_name'][\$sym]}'\");\n");
			}
		}
		$this->indent();
		$this->write("}\n");
	}

	function parser_nterm($name, $attrs, $skip, $check_only) {
  		if ($skip) {
			$this->indent();
			$this->write("\$sym = skip_$name(\$sym);\n");
		} else if ($check_only) {
			$this->indent();
			$this->write("\$sym = check_$name(\$sym);\n");
			$this->indent();
			$this->write("if (\$sym === false) {\n");
			$this->indent(1);
			$this->write("return false;\n");
			$this->indent();
			$this->write("}\n");
		} else {
			$this->indent();
			$this->write("\$sym = parse_$name(\$sym");
			if ($attrs !== null) {
				foreach($attrs as $attr) {
					$this->write(", $attr");
				}
			}
			$this->write(");\n");
		}
	}

	function parser_condition($set, $pred = null) {
		$this->write($this->gen_condition($set));
		if ($pred !== null) {
			if ($pred instanceof SyntaticPredicate) {
				$this->write(" && " . ($pred->neg ? "!" : "") . $pred->name . "(\$sym)");
			} else {
				$this->write(" && (" . $pred->code . ")");
			}
		}
	}

	function parser_if_condition($set, $pred = null, $first = true) {
		if ($first) {
			$this->indent();
			$this->write("if (");
		} else {
			$this->dec_indent();
			$this->indent();
			$this->write("} else if (");
		}
		$this->parser_condition($set, $pred);
		$this->write(") {\n");
		$this->inc_indent();
	}

	function parser_alt_if_condition($start, $state, $first = true) {
		if ($first) {
			$this->indent();
			$this->write("if (");
		} else {
			$this->dec_indent();
			$this->indent();
			$this->write("} else if (");
		}
		$this->write($this->gen_alt_condition($start, $state).") {\n");
		$this->inc_indent();
	}

	function parser_else() {
		$this->dec_indent();
		$this->indent();
		$this->write("} else {\n");
		$this->inc_indent();
	}

	function parser_end_if() {
		$this->dec_indent();
		$this->indent();
		$this->write("}\n");
	}

	function parser_while_condition($set, $pred = null) {
		$this->indent();
		$this->write("while (");
		$this->parser_condition($set, $pred);
		$this->write(") {\n");
		$this->inc_indent();
	}

	function parser_alt_while_condition($start, $state) {
		$this->indent();
		$this->write("if (".$this->gen_alt_neg_condition($start, $state).") {\n");
		$this->indent(1);
		$this->write("break;\n");
		$this->indent();
		$this->write("}\n");
	}

	function parser_start_while() {
		$this->indent();
		$this->write("while (1) {\n");
		$this->inc_indent();
	}

	function parser_end_while() {
		$this->dec_indent();
		$this->indent();
		$this->write("}\n");
	}

	function parser_do_until() {
		$this->indent();
		$this->write("do {\n");
		$this->inc_indent();
	}

	function parser_until_condition($set, $pred = null) {
		$this->dec_indent();
		$this->indent();
		$this->write("} while (");
		$this->parser_condition($set, $pred);
		$this->write(");\n");
	}

	function parser_alt_until_condition($start, $state) {
		$this->dec_indent();
		$this->indent();
		$this->write("} while (" . $this->gen_alt_condition($start, $state) . ");\n");
	}

	function parser_switch() {
		$this->indent();
		$this->write("switch (\$sym) {\n");
		$this->inc_indent();
	}

	function parser_alt_switch($state) {
		$this->indent();
		$this->write("switch (\$alt$state) {\n");
		$this->inc_indent();
	}

	function parser_start_case($set) {
		foreach($set as $sym => $dummy) {
			$this->indent();
			$this->write("case " . $this->grammar->term[$sym]->const_name . ":\n");
		}
		$this->inc_indent();
	}

	function parser_start_alt_case($state, $p) {
		$set = array();
		$this->collect_states($state, $p, $set);
		foreach($set as $alt => $dummy) {
			$this->indent();
			$this->write("case $alt:\n");
		}
		$this->inc_indent();
	}

	function parser_end_case() {
		$this->indent();
		$this->write("break;\n");
		$this->dec_indent();
	}

	function parser_default_case() {
		$this->indent();
		$this->write("default:\n");
		$this->inc_indent();
	}

	function parser_unexpected($check_only) {
		$this->dec_indent();
		$this->indent();
		$this->write("} else {\n");
		$this->indent(1);
		if ($check_only) {
			$this->write("return false;\n");
		} else {
			$this->write("error(\"unexpected '{\$GLOBALS['sym_name'][\$sym]}'\");\n");
		}
		$this->indent();
		$this->write("}\n");
	}

	function parser_unexpected_case($check_only) {
		$this->indent();
		$this->write("default:\n");
		$this->indent(1);
		if ($check_only) {
			$this->write("return false;\n");
		} else {
			$this->write("error(\"unexpected '{\$GLOBALS['sym_name'][\$sym]}'\");\n");
		}
		$this->dec_indent();
		$this->indent();
		$this->write("}\n");
	}

	function gen_attrs($attrs, $first) {
		$s = "";
		if (!empty($attrs)) {
			foreach ($attrs as $attr) {
				if ($first) {
					$first = false;
				} else {
					$s .= ", ";
				}
			  	$s .= $attr;
	  		}
	  	}
	  	return $s;
	}

	function main($func_name, $start_sym, $attrs) {
		$this->write("function $func_name(" . $this->gen_attrs($attrs, true) . ") {\n");
		$this->indent(1);
		$this->write("global \$pos, \$text;\n");
		if ($this->lineno) {
			$this->indent(1);
			$this->write("global \$line;\n");
		}
		$this->indent(1);
		$this->write("\$pos = \$text = 0;\n");
		if ($this->lineno) {
			$this->indent(1);
			$this->write("\$line = 1;\n");
		}
		$this->indent(1);
		$this->write("\$sym = parse_$start_sym(get_sym()" . $this->gen_attrs($attrs, false) . ");\n");
		$this->indent(1);
		$this->write("if (\$sym != YY_EOF) {\n");
		$this->indent(2);
		$this->write("error(\"<EOF> expected, got '{\$GLOBALS['sym_name'][\$sym]}'\");\n");
		$this->indent(1);
		$this->write("}\n");
		$this->write("}\n\n");
	}

	function parser_synpred_start($pred) {
		$this->indent();
		$this->write("function _{$pred->name}($sym) {\n");
		$this->inc_indent();
	}

	function parser_synpred_end($pred) {
		$this->indent();
		$this->write("return $sym;\n");
		$this->dec_indent();
		$this->indent();
		$this->write("}\n\n");
	}

	function parser_synpred($pred) {
		$this->indent();
		$this->write("function {$pred->name}(\$sym) {\n");
		$this->inc_indent();
		$this->indent();
		$this->write("\tglobal \$pos, \$text, \$line;\n");
		$this->write("\n");
		$this->save_pos();
		$this->indent();
		if (!$pred->start instanceof NonTerminal ||
		    $pred->start->next != null) {
			$this->write("\$ret = _{$pred->name}(\$sym) != false;\n");
		} else {
			$this->write("\$ret = check_" . $pred->start->name . "(\$sym) != false;\n");
		}
		$this->restore_pos();
		$this->indent();
		$this->write("return \$ret;\n");
		$this->dec_indent();
		$this->write("}\n\n");
	}

	function synpred($grammar, $pred, $scan) {
		if (!$pred->start instanceof NonTerminal ||
		    $pred->start->next != null) {
			$this->write("function check_" . $pred->name . "(\$sym) {\n");
			$this->gen_ast($grammar, $pred->name, "\t", $pred->start, array(), $scan, true);
			$this->write("\treturn \$sym;\n");
			$this->write("}\n\n");
		}
		$this->write("function " . $pred->name . "(\$sym) {\n");
		$this->write("\tglobal \$pos, \$text, \$line;\n");
		$this->write("\n");
		$this->save_pos();
		if (!$pred->start instanceof NonTerminal ||
		    $pred->start->next != null) {
			$this->write("\t\$sym = check_" . $pred->name . "(\$sym);\n");
		} else {
			$this->write("\t\$sym = check_" . $pred->start->name . "(\$sym);\n");
		}
		$this->restore_pos();
		$this->write("\treturn (\$sym !== false);\n");
		$this->write("}\n\n");
	}
}
