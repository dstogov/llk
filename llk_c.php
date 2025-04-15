<?php
class CEmitter extends Emitter {
	const IF_VS_CASE    = 4;
	const IF_VS_SET     = 4;
	const USE_GOTO      = true;
	const NEED_FORWARDS = true;
	const COMBINE_FINAL = true;

	function prologue_scanner($grammar) {
		$this->write("#define YYPOS cpos\n");
		$this->write("#define YYEND cend\n");
		$this->write("\n");

		foreach ($grammar->term as $term) {
			$this->write("#define $term->const_name $term->val\n");
		}
		$this->write("\n");

		$this->write("static const char * sym_name[] = {\n");
		foreach ($grammar->term as $term) {
			if ($term->special) {
				$this->write("\t\"<$term->name>\",\n");
			} else {
				$this->write("\t\"$term->name\",\n");
			}
		}
		$this->write("\tNULL\n");
		$this->write("};\n\n");

		if ($this->global_vars) {
			$this->write("static unsigned char *yy_buf;\n");
			$this->write("static unsigned const char *yy_end;\n");
			$this->write("static unsigned const char *yy_pos;\n");
			$this->write("static unsigned const char *yy_text;\n");
			if ($this->linepos) {
				$this->write("static unsigned const char *yy_linepos;\n");
			}
			if ($this->lineno) {
				$this->write("static int yy_line;\n");
			}
			$this->write("\n");
		}

		$this->write(<<<'EOF'
size_t yy_escape(char *buf, unsigned char ch)
{
	switch (ch) {
		case '\\': buf[0] = '\\'; buf[1] = '\\'; return 2;
		case '\'': buf[0] = '\\'; buf[1] = '\''; return 2;
		case '\"': buf[0] = '\\'; buf[1] = '\"'; return 2;
		case '\a': buf[0] = '\\'; buf[1] = '\a'; return 2;
		case '\b': buf[0] = '\\'; buf[1] = '\b'; return 2;
		case 27:   buf[0] = '\\'; buf[1] = 27; return 2;
		case '\f': buf[0] = '\\'; buf[1] = '\f'; return 2;
		case '\n': buf[0] = '\\'; buf[1] = '\n'; return 2;
		case '\r': buf[0] = '\\'; buf[1] = '\r'; return 2;
		case '\t': buf[0] = '\\'; buf[1] = '\t'; return 2;
		case '\v': buf[0] = '\\'; buf[1] = '\v'; return 2;
		case '\?': buf[0] = '\\'; buf[1] = 0x3f; return 2;
		default: break;
	}
	if (ch < 32 || ch >= 127) {
		buf[0] = '\\';
		buf[1] = '0' + ((ch >> 6) % 8);
		buf[2] = '0' + ((ch >> 3) % 8);
		buf[3] = '0' + (ch % 8);
		return 4;
	} else {
		buf[0] = ch;
		return 1;
	}
}

const char *yy_escape_char(char *buf, unsigned char ch)
{
	size_t len = yy_escape(buf, ch);
	buf[len] = 0;
	return buf;
}

const char *yy_escape_string(char *buf, size_t size, const unsigned char *str, size_t n)
{
	size_t i = 0;
	size_t pos = 0;
	size_t len;

	while (i < n) {
		if (pos + 8 > size) {
			buf[pos++] = '.';
			buf[pos++] = '.';
			buf[pos++] = '.';
			break;
		}
		len = yy_escape(buf + pos, str[i]);
		i++;
		pos += len;
	}
	buf[pos] = 0;
	return buf;
}


EOF
);
	}

	function prologue_parser($grammar) {
		$this->write("#define YY_IN_SET(sym, set, bitset) \\\n");
		$this->indent(1);
		$this->write("(bitset[sym>>3] & (1 << (sym & 0x7)))\n");
		$this->write("\n");
	}

	function prologue($grammar) {
		$this->grammar = $grammar;

		if (!$grammar->ignore_scanner) {
			$this->prologue_scanner($grammar);
		}

		if (!$grammar->ignore_parser) {
			$this->prologue_parser($grammar);
		}
	}

	function gen_escape_char($c) {
		if ($c === '\'') {
			return "\\'";
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
			return "YYPOS < YYEND";
		} else if (count($s) == 1) {
			if ($s[0] === "<EOF>") {
				return "YYPOS >= YYEND";
			} else {
				$c1 = $this->gen_escape_char($s[0]);
				return "ch == '$c1'";
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
					$r .= "ch == '$c1'";
					$m += 1;
				} else if ($i - $j == 2) {
					$c1 = $this->gen_escape_char($c1);
					$c2 = $this->gen_escape_char($c2);
					$r .= "ch == '$c1' || ch == '$c2'";
					$m += 2;
				} else {
					if (ord($c1) == 0) {
						$c2 = $this->gen_escape_char($c2);
						$r .= "YYPOS < YYEND && (ch <= '$c2'";
						$m += 2;
						$r2 = ")";
					} else if (ord($c2) == 255) {
						$c1 = $this->gen_escape_char($c1);
						$r .= "ch >= '$c1'";
						$m += 1;
					} else {
						$c1 = $this->gen_escape_char($c1);
						$c2 = $this->gen_escape_char($c2);
						$r .= "(ch >= '$c1' && ch <= '$c2')";
						$m += 2;
					}
				}
			}
			return $r . $r2;
		}
	}

	function gen_neg_charset_condition($s) {
		if (count($s) == 256) { // ANY
			return "YYPOS >= YYEND";
		} else if (count($s) == 1) {
			if ($s[0] === "<EOF>") {
				return "YYPOS < YYEND";
			} else {
				$c1 = $this->gen_escape_char($s[0]);
				return "ch != '$c1'";
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
					$r .= "ch != '$c1'";
					$m += 1;
				} else if ($i - $j == 2) {
					$c1 = $this->gen_escape_char($c1);
					$c2 = $this->gen_escape_char($c2);
					$r .= "ch != '$c1' && ch != '$c2'";
					$m += 2;
				} else {
					if (ord($c1) == 0) {
						$c2 = $this->gen_escape_char($c2);
						$r .= "ch > '$c2'";
						$m += 1;
					} else if (ord($c2) == 255) {
						$c1 = $this->gen_escape_char($c1);
						$r .= "ch < '$c1'";
						$m += 1;
					} else {
						$c1 = $this->gen_escape_char($c1);
						$c2 = $this->gen_escape_char($c2);
						$r .= "(ch < '$c1' || ch > '$c2')";
						$m += 2;
					}
				}
			}
			return $r;
		}
	}

	function scanner_start($func, $need_ret, $need_backtracking, $ctx) {
		$this->indent();
		$this->write("static int $func(void) {\n");
		$this->inc_indent();
		$this->indent();
		$this->write("char buf[64];\n");
		$this->indent();
		$this->write("int ch;\n");
		if (self::COMBINE_FINAL || $need_ret) {
			$this->indent();
			$this->write("int ret;\n");
		}

		if ($need_backtracking) {
			$this->indent();
			$this->write("int accept = -1;\n");
			$this->indent();
			$this->write("const unsigned char *accept_pos;\n");
		}

		$this->indent();
		$this->write("const unsigned char *cpos = yy_pos;\n");
		$this->indent();
		$this->write("const unsigned char *cend = yy_end;\n");
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("int state;\n");
		}
		if ($ctx) {
			$this->indent();
			$this->write("int ctx = 0;\n");
		}

		$this->write("\n");
		if (self::USE_GOTO) {
			$this->write("_yy_state_start:\n");
		}
		$this->indent();
		$this->write("yy_text = YYPOS;\n");
	}

	function scanner_loop_start() {
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("state = 0;\n");
			$this->indent();
			$this->write("while (1) {\n");
			$this->inc_indent();
			$this->indent();
			$this->write("ch = *YYPOS;\n");
			$this->indent();
			$this->write("switch (state) {\n");
			$this->inc_indent();
		} else {
			$this->indent();
			$this->write("ch = *YYPOS;\n");
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
				$this->write("ch = *++YYPOS;\n");
			}
			if ($tunnel_to) {
				$this->write("_yy_tunnel_$state:\n");
			}
		}
		if ($sym !== null) {
			$this->indent();
			$this->write("accept = " . $this->grammar->term[$sym]->const_name . ";\n");
			$this->indent();
			$this->write("accept_pos = YYPOS;\n");
		}
	}

	function scanner_state_switch_start() {
		$this->indent();
		$this->write("switch (ch) {\n");
		$this->inc_indent();
	}

	function scanner_state_switch_end() {
		$this->dec_indent();
		$this->indent();
		$this->write("}\n");
	}

	function scanner_state_tunnel_accept($state, $sym) {
		$this->indent();
		$this->write("ret = " . $this->grammar->term[$sym]->const_name . ";\n");
		$this->indent();
		if (!self::USE_GOTO) {
			$this->write("state = $state;\n");
		} else {
			$this->write("goto _yy_state_$state;\n");
		}
	}

	function scanner_state_condition($first, $set, $use_switch, $error_state, $ctx = false) {
		if ($use_switch) {
			foreach ($set as $ch) {
				$this->indent();
				if ($ch === "<EOF>") {
					$this->write("case '\\0':\n");
					$this->indent(1);
					if (self::USE_GOTO) {
						$this->write("if (ch == 0 && YYPOS < YYEND) goto _yy_state_error;\n");
					} else {
						$this->write("if (ch == 0 && YYPOS < YYEND) {state = $error_state; break;};\n");
					}
				} else {
					$ch = $this->gen_escape_char($ch);
					$this->write("case '$ch':\n");
				}
			}
		} else {
			$if = $first ? "if" : "} else if";
			$this->indent();
			$this->write("$if (" . $this->gen_charset_condition($set) . ") {\n");
		}
		$this->inc_indent();
		if ($ctx) {
			/* ignore line numbering when checking context */
		} else if ($this->lineno) {
			if (count($set) == 1 && $set[0] === "\n") {
				$this->indent();
				$this->write("yy_line++;\n");
				if ($this->linepos) {
					$this->indent();
					$this->write("yy_linepos = YYPOS + 1;\n");
				}
			} else if (in_array("\n", $set, true)) {
				$this->indent();
				$this->write("if (ch == '\\n') {\n");
				$this->indent(1);
				$this->write("yy_line++;\n");
				if ($this->linepos) {
					$this->indent(1);
					$this->write("yy_linepos = YYPOS + 1;\n");
				}
				$this->indent();
				$this->write("}\n");
			}
		} else if ($this->linepos) {
			if (count($set) == 1 && $set[0] === "\n") {
				$this->indent();
				$this->write("yy_linepos = YYPOS + 1;\n");
			} else if (in_array("\n", $set, true)) {
				$this->indent();
				$this->write("if (ch == '\\n') {\n");
				$this->indent(1);
				$this->write("yy_linepos = YYPOS + 1;\n");
				$this->indent();
				$this->write("}\n");
			}
		}
	}

	function scanner_state_tunnel_condition($set, $state, $sym) {
		$this->indent();
		$this->write("if (" . $this->gen_neg_charset_condition($set) . ") ");
		if ($sym !== null) {
			$this->write("{ret = " . $this->grammar->term[$sym]->const_name . "; ");
			if (self::USE_GOTO) {
				$this->write("goto _yy_tunnel_$state;}\n");
			} else {
				$this->write("state = $state; break;}\n");
			}
		} else {
			if (self::USE_GOTO) {
				$this->write("goto _yy_tunnel_$state;\n");
			} else {
				$this->write("{state = $state; break;}\n");
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
			$this->write("ctx++;\n");
		} else if ($prev_ctx) {
			$this->indent();
			$this->write("ctx = 0;\n");
		}
		if (!self::USE_GOTO || !$tunnel) {		
			$this->indent();
			$this->write("ch = *++YYPOS;\n");
			if ($sym !== null) {
				$this->indent();
				$this->write("accept = " . $this->grammar->term[$sym]->const_name . ";\n");
				$this->indent();
				$this->write("accept_pos = YYPOS;\n");
			}
		}
	}

	function scanner_state_transition($state, $target, $ctx, $prev_ctx) {
		if ($ctx) {
			$this->indent();
			$this->write("ctx++;\n");
		} else if ($prev_ctx) {
			$this->indent();
			$this->write("ctx = 0;\n");
		}
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("YYPOS++;\n");
			if ($state != $target) {
				$this->indent();
				$this->write("state = $target;\n");
			}
		} else {
			$this->indent();
			$this->write("goto _yy_state_$target;\n");
		}
	}

	function scanner_state_accept($sym, $ctx, $ignore = false) {
		if ($ctx) {
			$this->indent();
			$this->write("YYPOS -= ctx;\n");
		} else {
			$this->indent();
			$this->write("YYPOS++;\n");
		}
		if ($ignore) {
			$this->indent();
			$this->write("goto _yy_state_start;\n");
		} else if (self::COMBINE_FINAL) {
			$this->indent();
			$this->write("ret = " . $this->grammar->term[$sym]->const_name . ";\n");
			$this->indent();
			$this->write("goto _yy_fin;\n");
		} else {
			$this->indent();
			$this->write("yy_pos = YYPOS;\n");
			$this->indent();
			$this->write("return " . $this->grammar->term[$sym]->const_name . ";\n");
		}
	}

	function scanner_state_else_accept($sym, $use_switch, $ignore = false) {
		$this->indent();
		if ($use_switch) {
			$this->write("default:\n");
		} else {
			$this->write("} else {\n");
		}
		if ($ignore) {
			$this->indent(1);
			$this->write("goto _yy_state_start;\n");
		} else if (self::COMBINE_FINAL) {
			if ($sym !== null) {
				$this->indent(1);
				$this->write("ret = " . $this->grammar->term[$sym]->const_name . ";\n");
			}
			$this->indent(1);
			$this->write("goto _yy_fin;\n");
		} else {
			$this->indent(1);
			$this->write("yy_pos = YYPOS;\n");
			if ($sym !== null) {
				$this->indent(1);
				$this->write("return " . $this->grammar->term[$sym]->const_name . ";\n");
			} else {
				$this->indent(1);
				$this->write("return ret;\n");
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
			$this->write("state = $error_state;\n");
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
			$this->write("ret = " . $this->grammar->term[$sym]->const_name . ";\n");
		}
		$this->indent(1);
		if (!self::USE_GOTO) {
			$this->write("state = $tunnel_state;\n");
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
			$this->write("if (accept >= 0) {\n");
			$this->indent(1);
			$this->write("yy_pos = accept_pos;\n");
			$this->indent(1);
			$this->write("return accept;\n");
			$this->indent();
			$this->write("}\n");
		}

		$this->indent();
		$this->write("if (YYPOS >= YYEND) {\n");
		$this->indent(1);
		$this->write("yy_error(\"unexpected <EOF>\");\n");
		$this->indent();
		$this->write("} else if (YYPOS == yy_text) {\n");
		$this->indent(1);
		$this->write("yy_error_str(\"unexpected character\",  yy_escape_char(buf, ch));\n");
		$this->indent();
		$this->write("} else {\n");
		$this->indent(1);
		$this->write("yy_error_str(\"unexpected sequence\", yy_escape_string(buf, sizeof(buf), yy_text, 1 + YYPOS - yy_text));\n");
		$this->indent();
		$this->write("}\n");
		$this->indent();
		$this->write("YYPOS++;\n");
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("yy_text = YYPOS;\n");
			$this->indent();
			$this->write("state = 0;\n");
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
			$this->indent();
			$this->write("yy_pos = YYPOS;\n");
			$this->indent(0);
			$this->write("return ret;\n");
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
		$this->write("save_pos  = yy_pos;\n");
		$this->indent();
		$this->write("save_text = yy_text;\n");
		if ($this->linepos) {
			$this->indent();
			$this->write("save_linepos = yy_linepos;\n");
		}
		if ($this->lineno) {
			$this->indent();
			$this->write("save_line = yy_line;\n");
		}
	}

	function restore_pos() {
		$this->indent();
		$this->write("yy_pos  = save_pos;\n");
		$this->indent();
		$this->write("yy_text = save_text;\n");
		if ($this->linepos) {
			$this->indent();
			$this->write("yy_linepos = save_linepos;\n");
		}
		if ($this->lineno) {
			$this->indent();
			$this->write("yy_line = save_line;\n");
		}
	}

	function gen_condition($set, $neg = false) {
		if (count($set) == 0) {
			return $neg ? "0" : "1";
		} else if (count($set) <= self::IF_VS_SET) {
			foreach($set as $sym => $dummy) {
				if ($neg) {
					if (isset($s)) {
						$s .= " && sym != " . $this->grammar->term[$sym]->const_name;
					} else {
						$s = "sym != " . $this->grammar->term[$sym]->const_name;
					}
				} else {
					if (isset($s)) {
						$s .= " || sym == " . $this->grammar->term[$sym]->const_name;
					} else {
						$s = "sym == " . $this->grammar->term[$sym]->const_name;
					}
				}
			}
		} else {
			$n = (count($this->grammar->term) + (8 - 1)) >> 3;
			$bitset = str_repeat("\0", $n);
			if ($neg) {
				$s = "!YY_IN_SET(sym, (";
			} else {
				$s = "YY_IN_SET(sym, (";
			}
			$first = true;
			foreach($set as $sym => $dummy) {
				if ($first) {
					$first = false;
				} else {
					$s .= ",";
				}
				$s .= $this->grammar->term[$sym]->const_name;
				$val = $this->grammar->term[$sym]->val;
				$bitset[$val >> 3] = chr(ord($bitset[$val >> 3]) | (1 << ($val % 8)));
			}
			$s .= '), "';
			for ($i = 0; $i < $n; $i++) {
				$c = ord($bitset[$i]);
				$s .= "\\" . chr(ord('0') + (($c >> 6) % 8)) .
					chr(ord('0') + (($c >> 3) % 8)) .
					chr(ord('0') + ($c % 8));				
			}
			$s .= '")';
		}
		return $s;
	}

	function collect_states($state, $p, &$set) {
		while ($p != null &&
		       ($p instanceof Epsilon || $p instanceof Action || $p instanceof Predicate)) {
			$p = $p->next;
		}
		while ($p != null) {
			if ($p instanceof Alternative) {
				$q = $p;
				do {
					$this->collect_states($state, $q->start, $set);
					$q = $q->alt;
				} while ($q != null);
			} else if ($p instanceof Option) {
				$this->collect_states($state, $p->start, $set);
			} else if ($p instanceof Iteration) {
				$this->collect_states($state, $p->start, $set);
			} else if ($p->state != $state) {
				$set[$p->state] = 1;
				return;
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
			$s  .= "alt$state == $alt";
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
			$s  .= "alt$state != $alt";
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
						$s .= " && sym2 != " . $this->grammar->term[$sym]->const_name;
					} else {
						$s = "sym2 != " . $this->grammar->term[$sym]->const_name;
					}
				} else {
					if (isset($s)) {
						$s .= " || sym2 == " . $this->grammar->term[$sym]->const_name;
					} else {
						$s = "sym2 == " . $this->grammar->term[$sym]->const_name;
					}
				}
			}
		} else {
			$n = (count($this->grammar->term) + (8 - 1)) >> 3;
			$bitset = str_repeat("\0", $n);
			if ($neg) {
				$s = "!YY_IN_SET(sym2, (";
			} else {
				$s = "YY_IN_SET(sym2, (";
			}
			$first = true;
			foreach($set as $sym) {
				if ($first) {
					$first = false;
				} else {
					$s .= ",";
				}
				$s .= $this->grammar->term[$sym]->const_name;
				$val = $this->grammar->term[$sym]->val;
				$bitset[$val >> 3] = chr(ord($bitset[$val >> 3]) | (1 << ($val % 8)));
			}
			$s .= '), "';
			for ($i = 0; $i < $n; $i++) {
				$c = ord($bitset[$i]);
				$s .= "\\" . chr(ord('0') + (($c >> 6) % 8)) .
					chr(ord('0') + (($c >> 3) % 8)) .
					chr(ord('0') + ($c % 8));				
			}
			$s .= '")';
		}
		return $s;
	}

	function la_func() {
		$this->indent();
		$this->write("int   sym2;\n");
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("int   state;\n");
		}
		$this->indent();
		$this->write("const unsigned char *save_pos;\n");
		$this->indent();
		$this->write("const unsigned char *save_text;\n");
		if ($this->linepos) {
			$this->indent();
			$this->write("const unsigned char *save_linepos;\n");
		}
		if ($this->lineno) {
			$this->indent();
			$this->write("int   save_line;\n");
		}
	}

	function la_var($state) {
		$this->indent();
		$this->write("int alt$state;\n");
	}

	function la_loop_start($start) {
		$this->indent();
		$this->write("alt$start = -2;\n");
		$this->indent();
		$this->write("sym2 = sym;\n");
		if (!self::USE_GOTO) {
			$this->indent();
			$this->write("state = 0;\n");
			$this->indent();
			$this->write("while (1) {\n");
			$this->inc_indent();
			$this->indent();
			$this->write("switch (state) {\n");
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
			$this->write("alt$start = $alt;\n");
			$this->indent();
			$this->write("goto _yy_state_$start;\n");
		} else {
			$this->indent();
			$this->write("alt$start = $alt;\n");
			$this->indent();
			$this->write("goto _yy_state_$start;\n");
		}
		$this->dec_indent();
	}

	function la_state_transition($start, $state, $target, $get_sym) {
		if (!self::USE_GOTO) {
			if ($state != $target) {
				$this->indent();
				$this->write("state = $target;\n");
			}
		} else {
			$this->indent();
			$this->write("sym2 = $get_sym();\n");
			$this->indent();
			$this->write("goto _yy_state_$start"."_$target;\n");
		}
		$this->dec_indent();
	}

	function la_state_else_error($check_only) {
		$this->indent();
		$this->write("} else {\n");
		$this->indent(1);
		if ($check_only) {
			$this->write("return -1;\n");
		} else {
			$this->write("yy_error_sym(\"unexpected\", sym2);\n");
		}
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
			$this->write("sym2 = $get_sym();\n");
			$this->dec_indent();
			$this->indent();
			$this->write("}\n");
			$this->write("_yy_state_$start:\n");
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
		$this->write("static int $name(" . ($first ? "" : "int sym") . $this->gen_attrs($attrs, $first) . ") {\n");
		$this->inc_indent();
		if ($first) {
			$this->indent();
			$this->write("int sym;");
			$this->write("\n");
		}
	}

	function parser_forward_start() {
	}

	function parser_forward_func($name, $first, $attrs) {
		$this->indent();
		$this->write("static int $name(" . ($first ? "" : "int sym") . $this->gen_attrs($attrs, $first) . ");\n");
	}

	function parser_forward_end() {
		$this->write("\n");
	}

	function parser_func_end($name) {
		$this->indent();
		$this->write("return sym;\n");
		$this->dec_indent();
		$this->indent();
		$this->write("}\n\n");
	}

	function parser_get_sym($get_sym) {
		$this->indent();
		$this->write("sym = $get_sym();\n");
	}

	function parser_expect($sym, $check_only = false) {
		$this->indent();
		$this->write("if (sym != " . $this->grammar->term[$sym]->const_name . ") {\n");
		$this->indent(1);
		if ($check_only) {
			$this->write("return -1;\n");
		} else {
			if ($this->grammar->term[$sym]->special) {
				$this->write("yy_error_sym(\"<" . $this->grammar->term[$sym]->name . "> expected, got\", sym);\n");
			} else {
				$this->write("yy_error_sym(\"'$sym' expected, got\", sym);\n");
			}
		}
		$this->indent();
		$this->write("}\n");
	}

	function parser_nterm($name, $attrs, $skip, $check_only) {
  		if ($skip) {
			$this->indent();
			$this->write("sym = skip_$name(sym);\n");
		} else if ($check_only) {
			$this->indent();
			$this->write("sym = check_$name(sym);\n");
			$this->indent();
			$this->write("if (sym == -1) {\n");
			$this->indent(1);
			$this->write("return -1;\n");
			$this->indent();
			$this->write("}\n");
		} else {
			$this->indent();
			$this->write("sym = parse_$name(sym");
			if ($attrs !== null) {
				foreach($attrs as $attr) {
					$this->write(", $attr");
				}
			}
			$this->write(");\n");
		}
	}

	function parser_condition($set, $pred = null) {
		if ($pred === null) {
			$this->write($this->gen_condition($set));
		} else {
			$this->write("(" . $this->gen_condition($set) . ")");
			if ($pred instanceof SyntaticPredicate) {
				$this->write(" && " . ($pred->neg ? "!" : "") . $pred->name . "(sym)");
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
		$this->write("switch (sym) {\n");
		$this->inc_indent();
	}

	function parser_alt_switch($state) {
		$this->indent();
		$this->write("switch (alt$state) {\n");
		$this->inc_indent();
	}

	function parser_start_case($set) {
		foreach($set as $sym => $dummy) {
			$this->indent();
			$this->write("case " . $this->grammar->term[$sym]->const_name . ":\n");
		}
		$this->inc_indent();
	}

	function parser_default_case() {
		$this->indent();
		$this->write("default:\n");
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

	function parser_alt_exit_condition($start) {
		$this->dec_indent();
		$this->indent();
		$this->write("} else if (alt$start == -1) {\n");
		$this->inc_indent();
		$this->indent();
		$this->write("return sym;\n");
	}

	function parser_alt_exit_case() {
		$this->indent();
		$this->write("case -1:\n");
		$this->indent(1);
		$this->write("return sym;\n");
	}

	function parser_unexpected($check_only) {
		$this->dec_indent();
		$this->indent();
		$this->write("} else {\n");
		$this->indent(1);
		if ($check_only) {
			$this->write("return -1;\n");
		} else {
			$this->write("yy_error_sym(\"unexpected\", sym);\n");
		}
		$this->indent();
		$this->write("}\n");
	}

	function parser_unexpected_case($check_only) {
		$this->indent();
		$this->write("default:\n");
		$this->indent(1);
		if ($check_only) {
			$this->write("return -1;\n");
		} else {
			$this->write("yy_error_sym(\"unexpected\", sym);\n");
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
	  	} else if ($first) {
	  		$s .= "void";
	  	}
	  	return $s;
	}

	function extract_attr_name($attr) {
		$l = strlen($attr);
		if ($l == 0) {
			return $attr;
		}
		$i = $l;
		while ($i > 0) {
			$c = $attr[$i-1];
			if (!($c >= 'a' && $c <= 'z')
			 && !($c >= 'A' && $c <= 'Z')
			 && !($c >= '0' && $c <= '9')
			 && $c != '_') {
				break;
			}
			$i--;
		}
		if ($i == $l) {
			return $attr;
		}
		return substr($attr, $i);
	}

	function gen_main_attrs($attrs) {
		$s = "";
		if (!empty($attrs)) {
			foreach ($attrs as $attr) {
				$s .= ", " . $this->extract_attr_name($attr);
			}
		}
		return $s;
	}

	function main($func_name, $start_sym, $attrs) {
		$this->write("static void $func_name(" . $this->gen_attrs($attrs, true) . ") {\n");
		$this->write("\tint sym;\n");
		$this->write("\n");
		$this->write("\tyy_pos = yy_text = yy_buf;\n");
		if ($this->linepos) {
			$this->write("\tyy_linepos = yy_pos;\n");
		}
		if ($this->lineno) {
			$this->write("\tyy_line = 1;\n");
		}
		$this->write("\tsym = parse_$start_sym(get_sym()" . $this->gen_main_attrs($attrs) . ");\n");
		$this->write("\tif (sym != YY_EOF) {\n");
		$this->write("\t\tyy_error_sym(\"<EOF> expected, got\", sym);\n");
		$this->write("\t}\n");
		$this->write("}\n\n");
	}

	function parser_forward_synpred($name) {
		$this->indent();
		$this->write("static int $name(int sym);\n");
	}

	function parser_synpred_start($pred) {
		$this->indent();
		$this->write("static int _{$pred->name}(int sym) {\n");
		$this->inc_indent();
	}

	function parser_synpred_end($pred) {
		$this->indent();
		$this->write("return sym;\n");
		$this->dec_indent();
		$this->indent();
		$this->write("}\n\n");
	}

	function parser_synpred($pred) {
		$this->indent();
		$this->write("static int {$pred->name}(int sym) {\n");
		$this->inc_indent();
		$this->indent();
		$this->write("int ret;\n");
		$this->indent();
		$this->write("const unsigned char *save_pos;\n");
		$this->indent();
		$this->write("const unsigned char *save_text;\n");
		if ($this->linepos) {
			$this->indent();
			$this->write("const unsigned char *save_linepos;\n");
		}
		if ($this->lineno) {
			$this->indent();
			$this->write("int   save_line;\n");
		}
		$this->write("\n");
		$this->save_pos();
		$this->indent();
		if (!$pred->start instanceof NonTerminal ||
		    $pred->start->next != null) {
			$this->write("ret = _{$pred->name}(sym) != -1;\n");
		} else {
			$this->write("ret = check_" . $pred->start->name . "(sym) != -1;\n");
		}
		$this->restore_pos();
		$this->indent();
		$this->write("return ret;\n");
		$this->dec_indent();
		$this->write("}\n\n");
	}

}
