<?php
const DOT_DUMP_GRAMMAR = 1;
const DOT_DUMP_AST     = 2;

function ast_to_dot($f, &$n, $p, $from, $to, $dump_mode, $style="") {
	while ($p != null) {
		if ($p instanceof Terminal) {
			fwrite($f, "\tn$n [label=\"$p->name\",style=bold,shape=ellipse];\n");
			fwrite($f, "\tn$from -> n$n$style;\n");
			$from = $n;
			$style = "";
			$n++;
		} else if ($p instanceof NonTerminal) {
			fwrite($f, "\tn$n [label=\"$p->name\",shape=box];\n");
			fwrite($f, "\tn$from -> n$n$style;\n");
			$from = $n;
			$style = "";
			$n++;
		} else if ($p instanceof Alternative) {
			if ($dump_mode == DOT_DUMP_AST) {
				fwrite($f, "\tn$n [label=\"Alternative\",shape=diamond];\n");
			} else {
				fwrite($f, "\tn$n [label=\"\",shape=point];\n");
			}
			fwrite($f, "\tn$from -> n$n$style;\n");
			$alt_from = $n;
			$n++;
			if ($dump_mode == DOT_DUMP_AST) {
				$q = $p;
				while ($q != null) { /*for all alternatives*/
					ast_to_dot($f, $n, $q->start, $alt_from, -1, $dump_mode, " [style=dotted]");
					$q = $q->alt;
					if ($q != null) {
						fwrite($f, "\tn$n [label=\"Alternative\",shape=diamond];\n");
						fwrite($f, "\tn$alt_from -> n$n;\n");
						$alt_from = $n;
						$n++;
					}
				}
				$from = $alt_from;
			} else {
				fwrite($f, "\tn$n [label=\"\",shape=point];\n");
				$alt_to = $n;
				$n++;
				$q = $p;
				while ($q != null) { /*for all alternatives*/
					ast_to_dot($f, $n, $q->start, $alt_from, $alt_to, $dump_mode);
					$q = $q->alt;
				}
				$from = $alt_to;
			}
			$style = "";
		} else if ($p instanceof Option) {
			if ($dump_mode == DOT_DUMP_AST) {
				fwrite($f, "\tn$n [label=\"Option\",shape=diamond];\n");
			} else {
				fwrite($f, "\tn$n [label=\"\",shape=point];\n");
			}
			fwrite($f, "\tn$from -> n$n$style;\n");
			$opt_from = $n;
			$n++;
			if ($dump_mode == DOT_DUMP_AST) {
				ast_to_dot($f, $n, $p->start, $opt_from, -1, $dump_mode, " [style=dotted]");
				$from = $opt_from;
			} else {
				fwrite($f, "\tn$n [label=\"\",shape=point];\n");
				$opt_to = $n;
				$n++;
				ast_to_dot($f, $n, $p->start, $opt_from, $opt_to, $dump_mode);
				fwrite($f, "\tn$opt_from -> n$opt_to;\n");
				$from = $opt_to;
			}
			$style = "";
		} else if ($p instanceof Iteration) {
			if ($dump_mode == DOT_DUMP_AST) {
				fwrite($f, "\tn$n [label=\"Iteration\",shape=diamond];\n");
				fwrite($f, "\tn$from -> n$n$style;\n");
				$it_from = $n;
				$n++;
				ast_to_dot($f, $n, $p->start, $it_from, -1, $dump_mode, " [style=dotted]");
				$from = $it_from;
			} else {
				fwrite($f, "\tn$n [label=\"\",shape=point];\n");
				fwrite($f, "\tn$from -> n$n$style;\n");
				$it_from = $n;
				$n++;
				fwrite($f, "\tn$n [label=\"\",shape=point];\n");
				$it_to = $n;
				$n++;
				if ($p->min_count == 0) {
					ast_to_dot($f, $n, $p->start, $it_to, $it_from, $dump_mode);
					fwrite($f, "\tn$it_from -> n$it_to;\n");
				} else {
					ast_to_dot($f, $n, $p->start, $it_from, $it_to, $dump_mode);
					fwrite($f, "\tn$it_to -> n$it_from;\n");
				}
				$from = $it_to;
			}
			$style = "";
		} else if ($dump_mode == DOT_DUMP_AST) {
		}
		$p = $p->up ? null : $p->next;
	}
	if ($dump_mode != DOT_DUMP_AST) {
		fwrite($f, "\tn$from -> n$to;\n");
	}
}

function grammar_to_dots($grammar, $dump_mode = DOT_DUMP_AST) {
	foreach ($grammar->nonterm as $name => $nt) {
		if ($dump_mode == DOT_DUMP_GRAMMAR) {
			$f = fopen("$name.dot", "w");
			fwrite($f, "digraph $name {\n");
			fwrite($f, "\trankdir=LR;\n");
			fwrite($f, "\t{rank=min; n1 [label=\"START\",shape=point,rank=min];}\n");
			fwrite($f, "\t{rank=max; n2 [label=\"END\",shape=point,rank=max];}\n");
		  	$n = 3;
			ast_to_dot($f, $n, $nt->ast, 1, 2, $dump_mode);
			fwrite($f, "}\n");
			fclose($f);
		} else if ($dump_mode == DOT_DUMP_AST) {
			$f = fopen("$name.dot", "w");
			fwrite($f, "digraph $name {\n");
			fwrite($f, "\trankdir=LR;\n");
			fwrite($f, "\t{rank=min; n1 [label=\"START\",shape=point,rank=min];}\n");
		  	$n = 2;
			ast_to_dot($f, $n, $nt->ast, 1, -1, $dump_mode);
			fwrite($f, "}\n");
			fclose($f);
		}
	}
}

function la_fa_to_dots($grammar, $name, $fa, $term=false) {
	$states = [];
	$f = fopen("$name.dot", "w");
	fwrite($f, "digraph $name {\n");
	fwrite($f, "\trankdir=LR;\n");
	fwrite($f, "\t{rank=min; n1 [label=\"START\",shape=point,rank=min];}\n");
	fwrite($f, "\t{rank=max; n2 [label=\"END\",shape=point,rank=max];}\n");
	$n = 3;

	$states[$fa->start] = $n;
	fwrite($f, "\tn$n [label=\"$fa->start\",shape=diamond];\n");
	fwrite($f, "\tn1 -> n$n;\n");
	$n++;

	foreach($fa->final as $s => $dummy) {
		if (isset($states[$s])) {
			$sn = $states[$s];
		} else {
			$states[$s] = $sn = $n;
			fwrite($f, "\tn$n [label=\"$s\",shape=diamond];\n");
			$n++;
		}
		fwrite($f, "\tn$sn -> n2;\n");
	}

	foreach ($fa->move as $s1 => $v) {
		if (isset($states[$s1])) {
			$sn1 = $states[$s1];
		} else {
			$states[$s1] = $sn1 = $n;
			fwrite($f, "\tn$n [label=\"$s1\",shape=diamond];\n");
			$n++;
		}
		foreach ($v as $s2 => $s) {
			if (isset($states[$s2])) {
				$sn2 = $states[$s2];
			} else {
				$states[$s2] = $sn2 = $n;
				fwrite($f, "\tn$n [label=\"$s2\",shape=diamond];\n");
				$n++;
			}
//???		if (isset($fa->ctx[$s2])) {
//???			echo "CTX$";
//???		}
			if (is_array($s)) {
				if ($term && count($s) === 1) {
					foreach ($s as $c) {
						if ($c instanceof RegExp) {
							$label = $s->name;
							$shape = "ellipse";
						} else if ($grammar->term[$c]->special) {
							$label = "<" . $grammar->term[$c]->name . ">";
							$shape = "ellipse";
						} else {
							if (strpos($c, "nt:") === 0) {
								$label = substr($c, 3);
								$shape = "box";
							} else {
								$label = $c;
								$shape = "ellipse";
							}
						}
						fwrite($f, "\tn$n [label=\"$label\",style=bold,shape=$shape];\n");
						fwrite($f, "\tn$sn1 -> n$n;\n");
						fwrite($f, "\tn$n -> n$sn2;\n");
						$n++;
					}
				} else {
					foreach ($s as $c) {
						if ($s instanceof RegExp) {
							$label = $s->name;
							$shape = "ellipse";
						} else { /* string */
							if ($term) {
								if ($grammar->term[$c]->special) {
									$label = "<" . $grammar->term[$c]->name . ">";
									$shape = "ellipse";
								} else {
									if (strpos($c, "nt:") === 0) {
										$label = substr($c, 3);
										$shape = "box";
									} else {
										$label = $c;
										$shape = "elipse";
									}
								}
							} else {
								$i = ord($c);
								if ($i < 32 || $i >= 127) {
									$lavel = "\\" .
										chr((($i >> 6) & 3) + ord('0')) .
										chr((($i >> 3) & 7) + ord('0')) .
										chr(($i & 7) + ord('0'));
									$shape = "elipse";
								} else {
									$label = $c;
									$shape = "ellipse";
								}
							}
						}
						fwrite($f, "\tn$n [label=\"$label\",style=bold,shape=$shape];\n");
						fwrite($f, "\tn$sn1 -> n$n;\n");
						fwrite($f, "\tn$n -> n$sn2;\n");
						$n++;
					}
				}
			} else if ($s !== null) {
				if ($s instanceof RegExp) {
					$label = $s->name;
					$shape = "elipse";
				} else { /* string */
					if ($term) {
						if (strpos($s, "nt:") === 0) {
							$label = substr($s, 3);
							$shape = "box";
						} else {
							$label = $s;
							$shape = "ellipse";
						}
					} else {
						$label = $s;
						$shape = "ellipse";
					}
				}
				fwrite($f, "\tn$n [label=\"$label\",style=bold,shape=$shape];\n");
				fwrite($f, "\tn$sn1 -> n$n;\n");
				fwrite($f, "\tn$n -> n$sn2;\n");
				$n++;
			}
		}
	}
	fwrite($f, "}\n");
	fclose($f);
}
