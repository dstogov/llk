<?php
const YY_EOF = 0;
const YY__PERCENT_s_t_a_r_t = 1;
const YY__PERCENT_c_a_s_e_MINUS_s_e_n_s_e_t_i_v_e = 2;
const YY_TRUE = 3;
const YY_FALSE = 4;
const YY__PERCENT_g_l_o_b_a_l_MINUS_v_a_r_s = 5;
const YY__PERCENT_l_i_n_e_n_o = 6;
const YY__PERCENT_o_u_t_p_u_t = 7;
const YY__PERCENT_l_a_n_g_u_a_g_e = 8;
const YY__PERCENT_i_n_d_e_n_t = 9;
const YY__PERCENT_p_r_e_f_i_x = 10;
const YY_PROLOGUE = 11;
const YY_EPILOGUE = 12;
const YY__AT = 13;
const YY__RPAREN = 14;
const YY__COLON = 15;
const YY__SEMICOLON = 16;
const YY__COMMA = 17;
const YY_ATTR = 18;
const YY__BAR = 19;
const YY__AND = 20;
const YY__BANG = 21;
const YY__QUERY = 22;
const YY__QUERY_PLUS = 23;
const YY__QUERY_QUERY = 24;
const YY__PLUS = 25;
const YY__PLUS_PLUS = 26;
const YY__PLUS_QUERY = 27;
const YY__STAR = 28;
const YY__STAR_PLUS = 29;
const YY__STAR_QUERY = 30;
const YY__LPAREN = 31;
const YY__RBRACE = 32;
const YY__LBRACE = 33;
const YY_ACTION_CHAR = 34;
const YY_IDENT = 35;
const YY_IDENT_PLUS = 36;
const YY__SLASH = 37;
const YY__SLASH_SLASH = 38;
const YY__POINT = 39;
const YY__LBRACK = 40;
const YY__UPARROW = 41;
const YY__RBRACK = 42;
const YY_ESCAPE_CHAR = 43;
const YY_ESCAPE_CODE = 44;
const YY_SINGLE_CHAR = 45;
const YY__MINUS = 46;
const YY_STRING = 47;
const YY_EOL = 48;
const YY_WS = 49;
const YY_ONE_LINE_COMMENT = 50;
const YY_COMMENT = 51;

$sym_name = [
	"<EOF>",
	"%start",
	"%case-sensetive",
	"true",
	"false",
	"%global-vars",
	"%lineno",
	"%output",
	"%language",
	"%indent",
	"%prefix",
	"<prologue>",
	"<epilogue>",
	"@",
	")",
	":",
	";",
	",",
	"<attr>",
	"|",
	"&",
	"!",
	"?",
	"?+",
	"??",
	"+",
	"++",
	"+?",
	"*",
	"*+",
	"*?",
	"(",
	"}",
	"{",
	"<action_char>",
	"<ident>",
	"<ident_plus>",
	"/",
	"//",
	".",
	"[",
	"^",
	"]",
	"<escape_char>",
	"<escape_code>",
	"<single_char>",
	"-",
	"<string>",
	"<EOL>",
	"<WS>",
	"<ONE_LINE_COMMENT>",
	"<COMMENT>",
];

$buf = "";
$len = 0;
$pos = 0;
$text = 0;
$line = 1;

function escape_char($c) {
	if (ord($c) == ord('\'')) {
		return "\'";
	} else if (ord($c) == ord('\\')) {
		return "\\";
	} else if (ord($c) == ord("\r")) {
		return "\\r";
	} else if (ord($c) == ord("\n")) {
		return "\\n";
	} else if (ord($c) == ord("\t")) {
		return "\\t";
	} else if (ord($c) == ord("\v")) {
		return "\\v";
	} else if (ord($c) == ord("\f")) {
		return "\\f";
	} else if (ord($c) < ord(' ') || ord($c) >= 127) {
		$c = ord($c);
		return "\\" . chr(ord('0') + (($c >> 6) % 8)) .
			chr(ord('0') + (($c >> 3) % 8)) .
			chr(ord('0') + ($c % 8));
	} else {
		return $c;
	}
}

function escape_string($s) {
	$n = strlen($s);
	$r = "";
	for ($i = 0; $i < $n; $i++) {
		$r .= escape_char($s[$i]);
	}
	return $r;
}

function get_text($f = 0, $l = 0) {
	global $buf, $text, $pos;
	$text_len = $pos - $text;
	return ($text_len - $f - $l > 0) ? substr($buf, $text + $f, $text_len - $f - $l) : null;
}

function get_skip_sym() {
	global $buf, $pos, $len, $text;
	global $line;

	$text = $pos;
	$accept = null;
	$state = 0;
	while (1) {
		$ch = $buf[$pos];
		switch ($state) {
			case 0:
				switch ($ch) {
					case "%":
						$pos++;
						$ch = $buf[$pos];
						switch ($ch) {
							case "s":
								$pos++;
								$ch = $buf[$pos];
								if ($ch === "t") {
									$pos++;
									$ch = $buf[$pos];
									if ($ch === "a") {
										$pos++;
										$ch = $buf[$pos];
										if ($ch === "r") {
											$pos++;
											$ch = $buf[$pos];
											if ($ch === "t") {
												$pos++;
												return YY__PERCENT_s_t_a_r_t;
											} else {
												$state = 127;
											}
										} else {
											$state = 127;
										}
									} else {
										$state = 127;
									}
								} else {
									$state = 127;
								}
								break;
							case "c":
								$pos++;
								$ch = $buf[$pos];
								if ($ch === "a") {
									$pos++;
									$ch = $buf[$pos];
									if ($ch === "s") {
										$pos++;
										$ch = $buf[$pos];
										if ($ch === "e") {
											$pos++;
											$ch = $buf[$pos];
											if ($ch === "-") {
												$pos++;
												$ch = $buf[$pos];
												if ($ch === "s") {
													$pos++;
													$ch = $buf[$pos];
													if ($ch === "e") {
														$pos++;
														$ch = $buf[$pos];
														if ($ch === "n") {
															$pos++;
															$ch = $buf[$pos];
															if ($ch === "s") {
																$pos++;
																$ch = $buf[$pos];
																if ($ch === "e") {
																	$pos++;
																	$ch = $buf[$pos];
																	if ($ch === "t") {
																		$pos++;
																		$ch = $buf[$pos];
																		if ($ch === "i") {
																			$pos++;
																			$ch = $buf[$pos];
																			if ($ch === "v") {
																				$pos++;
																				$ch = $buf[$pos];
																				if ($ch === "e") {
																					$pos++;
																					return YY__PERCENT_c_a_s_e_MINUS_s_e_n_s_e_t_i_v_e;
																				} else {
																					$state = 127;
																				}
																			} else {
																				$state = 127;
																			}
																		} else {
																			$state = 127;
																		}
																	} else {
																		$state = 127;
																	}
																} else {
																	$state = 127;
																}
															} else {
																$state = 127;
															}
														} else {
															$state = 127;
														}
													} else {
														$state = 127;
													}
												} else {
													$state = 127;
												}
											} else {
												$state = 127;
											}
										} else {
											$state = 127;
										}
									} else {
										$state = 127;
									}
								} else {
									$state = 127;
								}
								break;
							case "g":
								$pos++;
								$ch = $buf[$pos];
								if ($ch === "l") {
									$pos++;
									$ch = $buf[$pos];
									if ($ch === "o") {
										$pos++;
										$ch = $buf[$pos];
										if ($ch === "b") {
											$pos++;
											$ch = $buf[$pos];
											if ($ch === "a") {
												$pos++;
												$ch = $buf[$pos];
												if ($ch === "l") {
													$pos++;
													$ch = $buf[$pos];
													if ($ch === "-") {
														$pos++;
														$ch = $buf[$pos];
														if ($ch === "v") {
															$pos++;
															$ch = $buf[$pos];
															if ($ch === "a") {
																$pos++;
																$ch = $buf[$pos];
																if ($ch === "r") {
																	$pos++;
																	$ch = $buf[$pos];
																	if ($ch === "s") {
																		$pos++;
																		return YY__PERCENT_g_l_o_b_a_l_MINUS_v_a_r_s;
																	} else {
																		$state = 127;
																	}
																} else {
																	$state = 127;
																}
															} else {
																$state = 127;
															}
														} else {
															$state = 127;
														}
													} else {
														$state = 127;
													}
												} else {
													$state = 127;
												}
											} else {
												$state = 127;
											}
										} else {
											$state = 127;
										}
									} else {
										$state = 127;
									}
								} else {
									$state = 127;
								}
								break;
							case "l":
								$pos++;
								$ch = $buf[$pos];
								if ($ch === "i") {
									$pos++;
									$ch = $buf[$pos];
									if ($ch === "n") {
										$pos++;
										$ch = $buf[$pos];
										if ($ch === "e") {
											$pos++;
											$ch = $buf[$pos];
											if ($ch === "n") {
												$pos++;
												$ch = $buf[$pos];
												if ($ch === "o") {
													$pos++;
													return YY__PERCENT_l_i_n_e_n_o;
												} else {
													$state = 127;
												}
											} else {
												$state = 127;
											}
										} else {
											$state = 127;
										}
									} else {
										$state = 127;
									}
								} else if ($ch === "a") {
									$pos++;
									$ch = $buf[$pos];
									if ($ch === "n") {
										$pos++;
										$ch = $buf[$pos];
										if ($ch === "g") {
											$pos++;
											$ch = $buf[$pos];
											if ($ch === "u") {
												$pos++;
												$ch = $buf[$pos];
												if ($ch === "a") {
													$pos++;
													$ch = $buf[$pos];
													if ($ch === "g") {
														$pos++;
														$ch = $buf[$pos];
														if ($ch === "e") {
															$pos++;
															return YY__PERCENT_l_a_n_g_u_a_g_e;
														} else {
															$state = 127;
														}
													} else {
														$state = 127;
													}
												} else {
													$state = 127;
												}
											} else {
												$state = 127;
											}
										} else {
											$state = 127;
										}
									} else {
										$state = 127;
									}
								} else {
									$state = 127;
								}
								break;
							case "o":
								$pos++;
								$ch = $buf[$pos];
								if ($ch === "u") {
									$pos++;
									$ch = $buf[$pos];
									if ($ch === "t") {
										$pos++;
										$ch = $buf[$pos];
										if ($ch === "p") {
											$pos++;
											$ch = $buf[$pos];
											if ($ch === "u") {
												$pos++;
												$ch = $buf[$pos];
												if ($ch === "t") {
													$pos++;
													return YY__PERCENT_o_u_t_p_u_t;
												} else {
													$state = 127;
												}
											} else {
												$state = 127;
											}
										} else {
											$state = 127;
										}
									} else {
										$state = 127;
									}
								} else {
									$state = 127;
								}
								break;
							case "i":
								$pos++;
								$ch = $buf[$pos];
								if ($ch === "n") {
									$pos++;
									$ch = $buf[$pos];
									if ($ch === "d") {
										$pos++;
										$ch = $buf[$pos];
										if ($ch === "e") {
											$pos++;
											$ch = $buf[$pos];
											if ($ch === "n") {
												$pos++;
												$ch = $buf[$pos];
												if ($ch === "t") {
													$pos++;
													return YY__PERCENT_i_n_d_e_n_t;
												} else {
													$state = 127;
												}
											} else {
												$state = 127;
											}
										} else {
											$state = 127;
										}
									} else {
										$state = 127;
									}
								} else {
									$state = 127;
								}
								break;
							case "p":
								$pos++;
								$ch = $buf[$pos];
								if ($ch === "r") {
									$pos++;
									$ch = $buf[$pos];
									if ($ch === "e") {
										$pos++;
										$ch = $buf[$pos];
										if ($ch === "f") {
											$pos++;
											$ch = $buf[$pos];
											if ($ch === "i") {
												$pos++;
												$ch = $buf[$pos];
												if ($ch === "x") {
													$pos++;
													return YY__PERCENT_p_r_e_f_i_x;
												} else {
													$state = 127;
												}
											} else {
												$state = 127;
											}
										} else {
											$state = 127;
										}
									} else {
										$state = 127;
									}
								} else {
									$state = 127;
								}
								break;
							case "{":
								$pos++;
								$state = 32;
								break;
							case "%":
								$pos++;
								$state = 33;
								break;
							default:
								$state = 127;
						}
						break;
					case "A":
					case "B":
					case "C":
					case "D":
					case "E":
					case "F":
					case "G":
					case "H":
					case "I":
					case "J":
					case "K":
					case "L":
					case "M":
					case "N":
					case "O":
					case "P":
					case "Q":
					case "R":
					case "S":
					case "T":
					case "U":
					case "V":
					case "W":
					case "X":
					case "Y":
					case "Z":
					case "a":
					case "b":
					case "c":
					case "d":
					case "e":
					case "g":
					case "h":
					case "i":
					case "j":
					case "k":
					case "l":
					case "m":
					case "n":
					case "o":
					case "p":
					case "q":
					case "r":
					case "s":
					case "u":
					case "v":
					case "w":
					case "x":
					case "y":
					case "z":
					case "_":
						$pos++;
						$state = 2;
						break;
					case "f":
						$pos++;
						$ch = $buf[$pos];
						if ($ch !== "a") {$state = 2; break;}
						$pos++;
						$ch = $buf[$pos];
						if ($ch !== "l") {$state = 2; break;}
						$pos++;
						$ch = $buf[$pos];
						if ($ch !== "s") {$state = 2; break;}
						$pos++;
						$ch = $buf[$pos];
						if ($ch !== "e") {$state = 2; break;}
						$pos++;
						$ret = YY_FALSE;
						$state = 126;
						break;
					case "t":
						$pos++;
						$ch = $buf[$pos];
						if ($ch !== "r") {$state = 2; break;}
						$pos++;
						$ch = $buf[$pos];
						if ($ch !== "u") {$state = 2; break;}
						$pos++;
						$ch = $buf[$pos];
						if ($ch !== "e") {$state = 2; break;}
						$pos++;
						$ret = YY_TRUE;
						$state = 126;
						break;
					case "\"":
						$pos++;
						$state = 5;
						break;
					case "'":
						$pos++;
						$state = 6;
						break;
					case "@":
						$pos++;
						return YY__AT;
						break;
					case ")":
						$pos++;
						return YY__RPAREN;
						break;
					case "{":
						$pos++;
						return YY__LBRACE;
						break;
					case "}":
						$pos++;
						return YY__RBRACE;
						break;
					case ":":
						$pos++;
						return YY__COLON;
						break;
					case "&":
						$pos++;
						return YY__AND;
						break;
					case "(":
						$pos++;
						return YY__LPAREN;
						break;
					case "!":
						$pos++;
						return YY__BANG;
						break;
					case "?":
						$pos++;
						$ch = $buf[$pos];
						if ($ch === "+") {
							$pos++;
							return YY__QUERY_PLUS;
						} else if ($ch === "?") {
							$pos++;
							return YY__QUERY_QUERY;
						} else {
							return YY__QUERY;
						}
						break;
					case "+":
						$pos++;
						$ch = $buf[$pos];
						if ($ch === "+") {
							$pos++;
							return YY__PLUS_PLUS;
						} else if ($ch === "?") {
							$pos++;
							return YY__PLUS_QUERY;
						} else {
							return YY__PLUS;
						}
						break;
					case "*":
						$pos++;
						$ch = $buf[$pos];
						if ($ch === "+") {
							$pos++;
							return YY__STAR_PLUS;
						} else if ($ch === "?") {
							$pos++;
							return YY__STAR_QUERY;
						} else {
							return YY__STAR;
						}
						break;
					case "|":
						$pos++;
						return YY__BAR;
						break;
					case "/":
						$pos++;
						$ch = $buf[$pos];
						$accept = YY__SLASH;
						$accept_pos = $pos;
						if ($ch === "/") {
							$pos++;
							$state = 50;
						} else if ($ch === "*") {
							$pos++;
							$state = 51;
						} else {
							return YY__SLASH;
						}
						break;
					case ";":
						$pos++;
						return YY__SEMICOLON;
						break;
					case "\r":
						$pos++;
						$ch = $buf[$pos];
						if ($ch === "\n") {
							$line++;
							$pos++;
							return YY_EOL;
						} else {
							return YY_EOL;
						}
						break;
					case "\n":
						$line++;
						$pos++;
						return YY_EOL;
						break;
					case " ":
					case "\t":
					case "\f":
					case "\v":
						$pos++;
						$state = 23;
						break;
					case "\000":
						if ($ch === "\000" && $pos < $len) {$state = 127; break;};
						$pos++;
						return YY_EOF;
						break;
					default:
						$state = 127;
				}
				break;
			case 2:
				if ($ch === "(") {
					$pos++;
					return YY_IDENT_PLUS;
				} else if (($ch >= "0" && $ch <= "9") || ($ch >= "A" && $ch <= "Z") || $ch === "_" || ($ch >= "a" && $ch <= "z")) {
					$pos++;
				} else {
					return YY_IDENT;
				}
				break;
			case 5:
				if ($ch === "\\") {
					$pos++;
					$ch = $buf[$pos];
					if ($pos < $len) {
						if ($ch === "\n") {
							$line++;
						}
						$pos++;
						$state = 5;
					} else {
						$state = 127;
					}
				} else if ($ch === "\"") {
					$pos++;
					return YY_STRING;
				} else if ($pos < $len && ($ch <= "!" || ($ch >= "#" && $ch <= "[") || $ch >= "]")) {
					if ($ch === "\n") {
						$line++;
					}
					$pos++;
				} else {
					$state = 127;
				}
				break;
			case 6:
				if ($ch === "\\") {
					$pos++;
					$ch = $buf[$pos];
					if ($pos < $len) {
						if ($ch === "\n") {
							$line++;
						}
						$pos++;
						$state = 6;
					} else {
						$state = 127;
					}
				} else if ($pos < $len && ($ch <= "&" || ($ch >= "(" && $ch <= "[") || $ch >= "]")) {
					if ($ch === "\n") {
						$line++;
					}
					$pos++;
				} else if ($ch === "'") {
					$pos++;
					return YY_STRING;
				} else {
					$state = 127;
				}
				break;
			case 23:
				if ($ch === "\t" || $ch === "\v" || $ch === "\f" || $ch === " ") {
					$pos++;
				} else {
					return YY_WS;
				}
				break;
			case 32:
				if ($ch === "%") {
					$pos++;
					$ch = $buf[$pos];
					if ($ch !== "}") {$state = 32; break;}
					$pos++;
					return YY_PROLOGUE;
				} else if ($pos < $len && ($ch <= "$" || $ch >= "&")) {
					if ($ch === "\n") {
						$line++;
					}
					$pos++;
				} else {
					$state = 127;
				}
				break;
			case 33:
				if ($pos < $len) {
					if ($ch === "\n") {
						$line++;
					}
					$pos++;
				} else {
					return YY_EPILOGUE;
				}
				break;
			case 50:
				if ($ch === "\r") {
					$pos++;
					$ch = $buf[$pos];
					if ($ch === "\n") {
						$line++;
						$pos++;
						return YY_ONE_LINE_COMMENT;
					} else {
						return YY_ONE_LINE_COMMENT;
					}
				} else if ($ch === "\n") {
					$line++;
					$pos++;
					return YY_ONE_LINE_COMMENT;
				} else if ($pos < $len && ($ch <= "\t" || $ch === "\v" || $ch === "\f" || $ch >= "\016")) {
					$pos++;
				} else {
					$state = 127;
				}
				break;
			case 51:
				if ($ch === "*") {
					$pos++;
					$ch = $buf[$pos];
					if ($ch !== "/") {$state = 51; break;}
					$pos++;
					return YY_COMMENT;
				} else if ($pos < $len && ($ch <= ")" || $ch >= "+")) {
					if ($ch === "\n") {
						$line++;
					}
					$pos++;
				} else {
					$state = 127;
				}
				break;
			case 126:
				if ($ch === "(") {
					$pos++;
					return YY_IDENT_PLUS;
				} else if (($ch >= "0" && $ch <= "9") || ($ch >= "A" && $ch <= "Z") || $ch === "_" || ($ch >= "a" && $ch <= "z")) {
					$pos++;
					$state = 2;
				} else {
					return $ret;
				}
				break;
			case 127:
				if ($accept !== null) {
					$pos = $accept_pos;
					return $accept;
				}
				if ($pos >= $len) {
					error("Unexpected <EOF>");
				} else if ($pos == $text) {
					error("Unexpected character '" . escape_char($ch) . "'");
				} else {
					error("Unexpected sequence '" . escape_string(substr($buf, $text, 1 + $pos - $text)) . "'");
				}
				$pos++;
				$text = $pos;
				$state = 0;
				break;
		}
	}
}

function skip_EOL($sym) {
	if ($sym != YY_EOL) {
		error("<EOL> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = get_skip_sym();
	return $sym;
}

function skip_WS($sym) {
	if ($sym != YY_WS) {
		error("<WS> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = get_skip_sym();
	return $sym;
}

function skip_ONE_LINE_COMMENT($sym) {
	if ($sym != YY_ONE_LINE_COMMENT) {
		error("<ONE_LINE_COMMENT> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = get_skip_sym();
	return $sym;
}

function skip_COMMENT($sym) {
	if ($sym != YY_COMMENT) {
		error("<COMMENT> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = get_skip_sym();
	return $sym;
}

function get_sym() {
	$sym = get_skip_sym();
	while ($sym == YY_EOL || $sym == YY_WS || $sym == YY_ONE_LINE_COMMENT || $sym == YY_COMMENT) {
		if ($sym == YY_EOL) {
			$sym = skip_EOL($sym);
		} else if ($sym == YY_WS) {
			$sym = skip_WS($sym);
		} else if ($sym == YY_ONE_LINE_COMMENT) {
			$sym = skip_ONE_LINE_COMMENT($sym);
		} else if ($sym == YY_COMMENT) {
			$sym = skip_COMMENT($sym);
		} else {
			error("unexpected '{$GLOBALS['sym_name'][$sym]}'");
		}
	}
	return $sym;
}

function ident_with_attrs_get_sym() {
	global $buf, $pos, $len, $text;
	global $line;

	$text = $pos;
	$state = 0;
	while (1) {
		$ch = $buf[$pos];
		switch ($state) {
			case 0:
				if (($ch >= "A" && $ch <= "Z") || $ch === "_" || ($ch >= "a" && $ch <= "z")) {
					$pos++;
					$state = 1;
				} else if ($ch === ",") {
					$pos++;
					return YY__COMMA;
				} else if ($ch === ")") {
					$pos++;
					return YY__RPAREN;
				} else if ($pos >= $len) {
					$pos++;
					return YY_EOF;
				} else {
					$state = 2;
				}
				break;
			case 1:
				if ($ch === "(") {
					$pos++;
					return YY_IDENT_PLUS;
				} else if (($ch >= "0" && $ch <= "9") || ($ch >= "A" && $ch <= "Z") || $ch === "_" || ($ch >= "a" && $ch <= "z")) {
					$pos++;
				} else {
					$state = 3;
				}
				break;
			case 2:
				if ($ch === "\t" || $ch === " ") {
					$pos++;
				} else if ($pos < $len && ($ch <= "\010" || $ch === "\v" || $ch === "\f" || ($ch >= "\016" && $ch <= "\037") || ($ch >= "!" && $ch <= "'") || $ch === "*" || $ch === "+" || $ch >= "-")) {
					$pos++;
					$state = 3;
				} else {
					$state = 12;
				}
				break;
			case 3:
				$ret = YY_ATTR;
				$state = 11;
				break;
			case 11:
				if ($ch === "\t" || $ch === " ") {
					$pos++;
					$state = 2;
				} else if ($pos < $len && ($ch <= "\010" || $ch === "\v" || $ch === "\f" || ($ch >= "\016" && $ch <= "\037") || ($ch >= "!" && $ch <= "'") || $ch === "*" || $ch === "+" || $ch >= "-")) {
					$pos++;
					$state = 3;
				} else {
					return $ret;
				}
				break;
			case 12:
				if ($pos >= $len) {
					error("Unexpected <EOF>");
				} else if ($pos == $text) {
					error("Unexpected character '" . escape_char($ch) . "'");
				} else {
					error("Unexpected sequence '" . escape_string(substr($buf, $text, 1 + $pos - $text)) . "'");
				}
				$pos++;
				$text = $pos;
				$state = 0;
				break;
		}
	}
}

function action_code_get_sym() {
	global $buf, $pos, $len, $text;
	global $line;

	$text = $pos;
	$state = 0;
	while (1) {
		$ch = $buf[$pos];
		switch ($state) {
			case 0:
				if ($ch === "{") {
					$pos++;
					return YY__LBRACE;
				} else if ($pos < $len && ($ch <= "z" || $ch === "|" || $ch >= "~")) {
					if ($ch === "\n") {
						$line++;
					}
					$pos++;
					return YY_ACTION_CHAR;
				} else if ($ch === "}") {
					$pos++;
					return YY__RBRACE;
				} else if ($pos >= $len) {
					$pos++;
					return YY_EOF;
				} else {
					$state = 5;
				}
				break;
			case 5:
				if ($pos >= $len) {
					error("Unexpected <EOF>");
				} else if ($pos == $text) {
					error("Unexpected character '" . escape_char($ch) . "'");
				} else {
					error("Unexpected sequence '" . escape_string(substr($buf, $text, 1 + $pos - $text)) . "'");
				}
				$pos++;
				$text = $pos;
				$state = 0;
				break;
		}
	}
}

function regexp2_get_sym() {
	global $buf, $pos, $len, $text;
	global $line;

	$text = $pos;
	$state = 0;
	while (1) {
		$ch = $buf[$pos];
		switch ($state) {
			case 0:
				switch ($ch) {
					case "/":
						$pos++;
						$ch = $buf[$pos];
						if ($ch === "/") {
							$pos++;
							return YY__SLASH_SLASH;
						} else {
							return YY__SLASH;
						}
						break;
					case ".":
						$pos++;
						return YY__POINT;
						break;
					case "\\":
						$pos++;
						$ch = $buf[$pos];
						if ($pos < $len && ($ch <= "/" || $ch >= "8")) {
							if ($ch === "\n") {
								$line++;
							}
							$pos++;
							return YY_ESCAPE_CHAR;
						} else if (($ch >= "0" && $ch <= "7")) {
							$pos++;
							$ch = $buf[$pos];
							if (($ch >= "0" && $ch <= "7")) {
								$pos++;
								$ch = $buf[$pos];
								if (($ch >= "0" && $ch <= "7")) {
									$pos++;
									return YY_ESCAPE_CODE;
								} else {
									return YY_ESCAPE_CODE;
								}
							} else {
								return YY_ESCAPE_CODE;
							}
						} else {
							$state = 27;
						}
						break;
					case "\000":
					case "\001":
					case "\002":
					case "\003":
					case "\004":
					case "\005":
					case "\006":
					case "\007":
					case "\010":
					case "\t":
					case "\n":
					case "\v":
					case "\f":
					case "\r":
					case "\016":
					case "\017":
					case "\020":
					case "\021":
					case "\022":
					case "\023":
					case "\024":
					case "\025":
					case "\026":
					case "\027":
					case "\030":
					case "\031":
					case "\032":
					case "\033":
					case "\034":
					case "\035":
					case "\036":
					case "\037":
					case " ":
					case "!":
					case "\"":
					case "#":
					case "$":
					case "%":
					case "&":
					case "'":
					case ",":
					case "0":
					case "1":
					case "2":
					case "3":
					case "4":
					case "5":
					case "6":
					case "7":
					case "8":
					case "9":
					case ":":
					case ";":
					case "<":
					case "=":
					case ">":
					case "@":
					case "A":
					case "B":
					case "C":
					case "D":
					case "E":
					case "F":
					case "G":
					case "H":
					case "I":
					case "J":
					case "K":
					case "L":
					case "M":
					case "N":
					case "O":
					case "P":
					case "Q":
					case "R":
					case "S":
					case "T":
					case "U":
					case "V":
					case "W":
					case "X":
					case "Y":
					case "Z":
					case "_":
					case "`":
					case "a":
					case "b":
					case "c":
					case "d":
					case "e":
					case "f":
					case "g":
					case "h":
					case "i":
					case "j":
					case "k":
					case "l":
					case "m":
					case "n":
					case "o":
					case "p":
					case "q":
					case "r":
					case "s":
					case "t":
					case "u":
					case "v":
					case "w":
					case "x":
					case "y":
					case "z":
					case "{":
					case "}":
					case "~":
					case "\177":
					case "\200":
					case "\201":
					case "\202":
					case "\203":
					case "\204":
					case "\205":
					case "\206":
					case "\207":
					case "\210":
					case "\211":
					case "\212":
					case "\213":
					case "\214":
					case "\215":
					case "\216":
					case "\217":
					case "\220":
					case "\221":
					case "\222":
					case "\223":
					case "\224":
					case "\225":
					case "\226":
					case "\227":
					case "\230":
					case "\231":
					case "\232":
					case "\233":
					case "\234":
					case "\235":
					case "\236":
					case "\237":
					case "\240":
					case "\241":
					case "\242":
					case "\243":
					case "\244":
					case "\245":
					case "\246":
					case "\247":
					case "\250":
					case "\251":
					case "\252":
					case "\253":
					case "\254":
					case "\255":
					case "\256":
					case "\257":
					case "\260":
					case "\261":
					case "\262":
					case "\263":
					case "\264":
					case "\265":
					case "\266":
					case "\267":
					case "\270":
					case "\271":
					case "\272":
					case "\273":
					case "\274":
					case "\275":
					case "\276":
					case "\277":
					case "\300":
					case "\301":
					case "\302":
					case "\303":
					case "\304":
					case "\305":
					case "\306":
					case "\307":
					case "\310":
					case "\311":
					case "\312":
					case "\313":
					case "\314":
					case "\315":
					case "\316":
					case "\317":
					case "\320":
					case "\321":
					case "\322":
					case "\323":
					case "\324":
					case "\325":
					case "\326":
					case "\327":
					case "\330":
					case "\331":
					case "\332":
					case "\333":
					case "\334":
					case "\335":
					case "\336":
					case "\337":
					case "\340":
					case "\341":
					case "\342":
					case "\343":
					case "\344":
					case "\345":
					case "\346":
					case "\347":
					case "\350":
					case "\351":
					case "\352":
					case "\353":
					case "\354":
					case "\355":
					case "\356":
					case "\357":
					case "\360":
					case "\361":
					case "\362":
					case "\363":
					case "\364":
					case "\365":
					case "\366":
					case "\367":
					case "\370":
					case "\371":
					case "\372":
					case "\373":
					case "\374":
					case "\375":
					case "\376":
					case "\377":
						if ($ch === "\n") {
							$line++;
						}
						$pos++;
						return YY_SINGLE_CHAR;
						break;
					case "[":
						$pos++;
						return YY__LBRACK;
						break;
					case "^":
						$pos++;
						return YY__UPARROW;
						break;
					case "-":
						$pos++;
						return YY__MINUS;
						break;
					case "]":
						$pos++;
						return YY__RBRACK;
						break;
					case "(":
						$pos++;
						return YY__LPAREN;
						break;
					case ")":
						$pos++;
						return YY__RPAREN;
						break;
					case "?":
						$pos++;
						$ch = $buf[$pos];
						if ($ch === "+") {
							$pos++;
							return YY__QUERY_PLUS;
						} else if ($ch === "?") {
							$pos++;
							return YY__QUERY_QUERY;
						} else {
							return YY__QUERY;
						}
						break;
					case "+":
						$pos++;
						$ch = $buf[$pos];
						if ($ch === "+") {
							$pos++;
							return YY__PLUS_PLUS;
						} else if ($ch === "?") {
							$pos++;
							return YY__PLUS_QUERY;
						} else {
							return YY__PLUS;
						}
						break;
					case "*":
						$pos++;
						$ch = $buf[$pos];
						if ($ch === "+") {
							$pos++;
							return YY__STAR_PLUS;
						} else if ($ch === "?") {
							$pos++;
							return YY__STAR_QUERY;
						} else {
							return YY__STAR;
						}
						break;
					case "|":
						$pos++;
						return YY__BAR;
						break;
					case "\000":
						if ($ch === "\000" && $pos < $len) {$state = 27; break;};
						$pos++;
						return YY_EOF;
						break;
					default:
						$state = 27;
				}
				break;
			case 27:
				if ($pos >= $len) {
					error("Unexpected <EOF>");
				} else if ($pos == $text) {
					error("Unexpected character '" . escape_char($ch) . "'");
				} else {
					error("Unexpected sequence '" . escape_string(substr($buf, $text, 1 + $pos - $text)) . "'");
				}
				$pos++;
				$text = $pos;
				$state = 0;
				break;
		}
	}
}

function parse_grammar($sym, $grammar) {
	while (in_array($sym, array(YY__PERCENT_s_t_a_r_t,YY__PERCENT_c_a_s_e_MINUS_s_e_n_s_e_t_i_v_e,YY__PERCENT_g_l_o_b_a_l_MINUS_v_a_r_s,YY__PERCENT_l_i_n_e_n_o,YY__PERCENT_o_u_t_p_u_t,YY__PERCENT_l_a_n_g_u_a_g_e,YY__PERCENT_i_n_d_e_n_t,YY__PERCENT_p_r_e_f_i_x))) {
		$sym = parse_declaration($sym, $grammar);
	}
	if ($sym == YY_PROLOGUE) {
		$sym = parse_prologue($sym, $grammar);
	}
	while ($sym == YY__AT || $sym == YY_IDENT || $sym == YY_IDENT_PLUS) {
		$sym = parse_rule($sym, $grammar);
	}
	if ($sym == YY_EPILOGUE) {
		$sym = parse_epilogue($sym, $grammar);
	}
	return $sym;
}

function parse_declaration($sym, $grammar) {
	switch ($sym) {
		case YY__PERCENT_s_t_a_r_t:
			$sym = get_sym();
			$sym = parse_ident($sym, $id);
			$grammar->start = $id;
			break;
		case YY__PERCENT_c_a_s_e_MINUS_s_e_n_s_e_t_i_v_e:
			$sym = get_sym();
			if ($sym == YY_TRUE) {
				$sym = get_sym();
				$grammar->case_sensetive = true;
			} else if ($sym == YY_FALSE) {
				$sym = get_sym();
				$grammar->case_sensetive = false;
			}
			break;
		case YY__PERCENT_g_l_o_b_a_l_MINUS_v_a_r_s:
			$sym = get_sym();
			if ($sym == YY_TRUE) {
				$sym = get_sym();
				$grammar->global_vars = true;
			} else if ($sym == YY_FALSE) {
				$sym = get_sym();
				$grammar->global_vars = false;
			}
			break;
		case YY__PERCENT_l_i_n_e_n_o:
			$sym = get_sym();
			if ($sym == YY_TRUE) {
				$sym = get_sym();
				$grammar->leneno = true;
			} else if ($sym == YY_FALSE) {
				$sym = get_sym();
				$grammar->lineno = false;
			}
			break;
		case YY__PERCENT_o_u_t_p_u_t:
			$sym = get_sym();
			$sym = parse_string($sym, $s);
			$grammar->output = $s;
			break;
		case YY__PERCENT_l_a_n_g_u_a_g_e:
			$sym = get_sym();
			$sym = parse_string($sym, $s);
			$grammar->language = $s;
			break;
		case YY__PERCENT_i_n_d_e_n_t:
			$sym = get_sym();
			$sym = parse_string($sym, $s);
			$grammar->indent = $s;
			break;
		case YY__PERCENT_p_r_e_f_i_x:
			$sym = get_sym();
			$sym = parse_string($sym, $s);
			$grammar->prefix = $s;
			break;
	}
	return $sym;
}

function parse_prologue($sym, $grammar) {
	if ($sym != YY_PROLOGUE) {
		error("<prologue> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$grammar->prologue = ltrim(get_text(2, 2));
	$sym = get_sym();
	return $sym;
}

function parse_epilogue($sym, $grammar) {
	if ($sym != YY_EPILOGUE) {
		error("<epilogue> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$grammar->epilogue = ltrim(get_text(2, 0));
	$sym = get_sym();
	return $sym;
}

function parse_rule($sym, $grammar) {
	$lexer = false;
	if ($sym == YY__AT) {
		$sym = get_sym();
		$lexer = true;
	}
	if ($sym == YY_IDENT) {
		$sym = parse_ident($sym, $id);
		$attrs = null;
	} else if ($sym == YY_IDENT_PLUS) {
		$sym = parse_ident_with_attrs($sym, $id, $attrs);
		if ($sym != YY__RPAREN) {
			error("')' expected, got '{$GLOBALS['sym_name'][$sym]}'");
		}
		$sym = get_sym();
	} else {
		error("unexpected '{$GLOBALS['sym_name'][$sym]}'");
	}
	$code = null;
	if ($sym == YY__LBRACE) {
		$sym = parse_action($sym, $code);
	}
	if ($sym != YY__COLON) {
		error("':' expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = get_sym();
	if (in_array($sym, array(YY__AND,YY__BANG,YY__QUERY,YY_STRING,YY_IDENT,YY_IDENT_PLUS,YY__LPAREN,YY__LBRACE,YY__BAR,YY__SEMICOLON))) {
		$sym = parse_expression($sym, $grammar, $gl, $gr);
		complete_graph($gr);
				$nt = new NonTermDef($id, $attrs, $code, $gl);
				if ($lexer) {
					$nt->lexer = new LexerDef($id . "_get_sym", $id . "_get_sym");
				}
				if (!isset($grammar->nonterm[$id])) {
					$grammar->nonterm[$id] = $nt;
				} else {
					error("'$id' already defined");
				}
	} else if ($sym == YY__SLASH) {
		$sym = parse_regexp($sym, $gl, $gr);
		$rcode = null;
		if ($sym == YY__LBRACE) {
			$sym = parse_action($sym, $rcode);
		}
		complete_graph($gr);
				$r = new RegExp($id, $gl);
				$r->code = $rcode;
				if (!isset($grammar->term[$id])) {
					$grammar->term[$id] = new TermDef($id, count($grammar->term), true);
				} else {
					error("terminal '$id' already defined");
				}
				$nt = new NonTermDef($id, $attrs, $code, $r);
				$gl->name = $id;
				if (!isset($grammar->nonterm[$id])) {
					$grammar->nonterm[$id] = $nt;
				} else {
					error("non-terminal '$id' already defined");
				}
	} else {
		error("unexpected '{$GLOBALS['sym_name'][$sym]}'");
	}
	if ($sym != YY__SEMICOLON) {
		error("';' expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = get_sym();
	return $sym;
}

function parse_ident_with_attrs($sym, &$id, &$attrs) {
	$sym = parse_ident_plus($sym, $id);
	$attrs = array();
	if ($sym == YY_ATTR) {
		$sym = parse_attr($sym, $attr);
		$attrs[] = $attr;
		while ($sym == YY__COMMA) {
			$sym = ident_with_attrs_get_sym();
			$sym = parse_attr($sym, $attr);
			$attrs[] = $attr;
		}
	}
	return $sym;
}

function parse_attr($sym, &$attr) {
	if ($sym != YY_ATTR) {
		error("<attr> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$attr = trim(get_text());
	$sym = ident_with_attrs_get_sym();
	return $sym;
}

function parse_expression($sym, $grammar, &$gl, &$gr) {
	$first = true;
	$sym = parse_alternative($sym, $grammar, $gl, $gr);
	while ($sym == YY__BAR) {
		$sym = get_sym();
		$gl2 = $gr2 = null;
		$sym = parse_alternative($sym, $grammar, $gl2, $gr2);
		if ($first) {
					make_first_alt($gl, $gr);
					$first = false;
				}
				concat_alt($gl, $gr, $gl2, $gr2);
	}
	return $sym;
}

function parse_alternative($sym, $grammar, &$gl, &$gr) {
	$first = true;
	if ($sym == YY__AND || $sym == YY__BANG || $sym == YY__QUERY) {
		if ($sym == YY__AND) {
			$sym = get_sym();
			$sym = parse_factor($sym, $grammar, $gl2, $gr2);
			$gl = $gr = make_pred($gl2, $gr2);
				if (!isset($grammar->pred[$gl->name])) {
					$grammar->pred[$gl->name] = $gl;
				}
				$first = false;
		} else if ($sym == YY__BANG) {
			$sym = get_sym();
			$sym = parse_factor($sym, $grammar, $gl2, $gr2);
			$gl = $gr = make_pred($gl2, $gr2, true);
				if (!isset($grammar->pred[$gl->name])) {
					$grammar->pred[$gl->name] = $gl;
				}
				$first = false;
		} else if ($sym == YY__QUERY) {
			$sym = get_sym();
			$sym = parse_action($sym, $code);
			$gl = $gr = new SemanticPredicate($code);
				$first = false;
		} else {
			error("unexpected '{$GLOBALS['sym_name'][$sym]}'");
		}
	}
	while (in_array($sym, array(YY_STRING,YY_IDENT,YY_IDENT_PLUS,YY__LPAREN,YY__LBRACE))) {
		if ($sym == YY_STRING || $sym == YY_IDENT || $sym == YY_IDENT_PLUS || $sym == YY__LPAREN) {
			$sym = parse_term($sym, $grammar, $gl2, $gr2);
		} else if ($sym == YY__LBRACE) {
			$sym = parse_action($sym, $code);
			$gl2 = $gr2 = new Action($code);
		} else {
			error("unexpected '{$GLOBALS['sym_name'][$sym]}'");
		}
		if ($first) {
					$gl = $gl2; $gr = $gr2; $first = false;
				} else {
					concat_seq($gl, $gr, $gl2, $gr2);
				}
	}
	if ($first) {
				$gl = $gr = new Epsilon();
			}
	return $sym;
}

function parse_term($sym, $grammar, &$gl, &$gr) {
	$sym = parse_factor($sym, $grammar, $gl, $gr);
	if (in_array($sym, array(YY__QUERY,YY__QUERY_PLUS,YY__QUERY_QUERY,YY__PLUS,YY__PLUS_PLUS,YY__PLUS_QUERY,YY__STAR,YY__STAR_PLUS,YY__STAR_QUERY))) {
		switch ($sym) {
			case YY__QUERY:
				$sym = get_sym();
				make_opt($gl, $gr);
				break;
			case YY__QUERY_PLUS:
				$sym = get_sym();
				make_opt($gl, $gr, true);
				break;
			case YY__QUERY_QUERY:
				$sym = get_sym();
				make_opt($gl, $gr, false);
				break;
			case YY__PLUS:
				$sym = get_sym();
				make_iter(1, $gl, $gr);
				break;
			case YY__PLUS_PLUS:
				$sym = get_sym();
				make_iter(1, $gl, $gr, true);
				break;
			case YY__PLUS_QUERY:
				$sym = get_sym();
				make_iter(1, $gl, $gr, false);
				break;
			case YY__STAR:
				$sym = get_sym();
				make_iter(0, $gl, $gr);
				break;
			case YY__STAR_PLUS:
				$sym = get_sym();
				make_iter(0, $gl, $gr, true);
				break;
			case YY__STAR_QUERY:
				$sym = get_sym();
				make_iter(0, $gl, $gr, false);
				break;
		}
	}
	return $sym;
}

function parse_factor($sym, $grammar, &$gl, &$gr) {
	if ($sym == YY_STRING) {
		$sym = parse_terminal($sym, $t);
		$gl = $gr = new Terminal($t);
			if (!isset($grammar->term[$t])) {
				$grammar->term[$t] = new TermDef($t, count($grammar->term));
			}
	} else if ($sym == YY_IDENT || $sym == YY_IDENT_PLUS) {
		$sym = parse_nonterminal($sym, $grammar, $nt);
		$gl = $gr = $nt;
	} else if ($sym == YY__LPAREN) {
		$sym = get_sym();
		$sym = parse_expression($sym, $grammar, $gl, $gr);
		if ($sym != YY__RPAREN) {
			error("')' expected, got '{$GLOBALS['sym_name'][$sym]}'");
		}
		$sym = get_sym();
	}
	return $sym;
}

function parse_nonterminal($sym, $grammar, &$nt) {
	if ($sym == YY_IDENT) {
		$sym = parse_ident($sym, $id);
		$attrs = null;
	} else if ($sym == YY_IDENT_PLUS) {
		$sym = parse_ident_with_attrs($sym, $id, $attrs);
		if ($sym != YY__RPAREN) {
			error("')' expected, got '{$GLOBALS['sym_name'][$sym]}'");
		}
		$sym = get_sym();
	} else {
		error("unexpected '{$GLOBALS['sym_name'][$sym]}'");
	}
	$nt = new NonTerminal($id);
			$nt->attrs = $attrs;
			$grammar->used[$id] = 1;
	return $sym;
}

function parse_terminal($sym, &$s) {
	$sym = parse_string($sym, $s);
	return $sym;
}

function parse_action($sym, &$code) {
	$sym = parse_action_code($sym, $code);
	if ($sym != YY__RBRACE) {
		error("'}' expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = get_sym();
	return $sym;
}

function parse_action_code($sym, &$code) {
	if ($sym != YY__LBRACE) {
		error("'{' expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = action_code_get_sym();
	$code = "";
	while ($sym == YY_ACTION_CHAR || $sym == YY__LBRACE) {
		if ($sym == YY_ACTION_CHAR) {
			$sym = parse_action_char($sym, $ch);
			$code .= $ch;
		} else if ($sym == YY__LBRACE) {
			$sym = parse_action_code($sym, $s);
			if ($sym != YY__RBRACE) {
				error("'}' expected, got '{$GLOBALS['sym_name'][$sym]}'");
			}
			$sym = action_code_get_sym();
			$code .= "{" . $s . "}";
		} else {
			error("unexpected '{$GLOBALS['sym_name'][$sym]}'");
		}
	}
	return $sym;
}

function parse_action_char($sym, &$ch) {
	if ($sym != YY_ACTION_CHAR) {
		error("<action_char> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$ch = get_text();
	$sym = action_code_get_sym();
	return $sym;
}

function parse_ident($sym, &$id) {
	if ($sym != YY_IDENT) {
		error("<ident> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$id = get_text();
	$sym = get_sym();
	return $sym;
}

function parse_ident_plus($sym, &$id) {
	if ($sym != YY_IDENT_PLUS) {
		error("<ident_plus> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$id = get_text(0, 1);
	$sym = ident_with_attrs_get_sym();
	return $sym;
}

function parse_regexp($sym, &$gl, &$gr) {
	$sym = parse_regexp2($sym, $gl, $gr);
	if ($sym != YY__SLASH) {
		error("'/' expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = get_sym();
	return $sym;
}

function parse_regexp2($sym, &$gl, &$gr) {
	if ($sym != YY__SLASH) {
		error("'/' expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$sym = regexp2_get_sym();
	$sym = parse_regex($sym, $gl, $gr);
	return $sym;
}

function parse_regex($sym, &$gl, &$gr) {
	$first = true;
	$sym = parse_regex_alt($sym, $gl, $gr);
	while ($sym == YY__BAR) {
		$sym = regexp2_get_sym();
		$sym = parse_regex_alt($sym, $gl2, $gr2);
		if ($first) {
					make_first_alt($gl, $gr);
					$first = false;
				}
				concat_alt($gl, $gr, $gl2, $gr2);
	}
	return $sym;
}

function parse_regex_alt($sym, &$gl, &$gr) {
	if (in_array($sym, array(YY__POINT,YY_ESCAPE_CHAR,YY_ESCAPE_CODE,YY_SINGLE_CHAR,YY__LBRACK,YY__LPAREN))) {
		$first = true;
		do {
			$sym = parse_regex_term($sym, $gl2, $gr2);
			if ($first) {
						$gl = $gl2; $gr = $gr2; $first = false;
					} else {
						concat_seq($gl, $gr, $gl2, $gr2);
					}
		} while (in_array($sym, array(YY__POINT,YY_ESCAPE_CHAR,YY_ESCAPE_CODE,YY_SINGLE_CHAR,YY__LBRACK,YY__LPAREN)));
		if ($sym == YY__SLASH_SLASH) {
			$sym = regexp2_get_sym();
			$sym = parse_regex_term($sym, $gl2, $gr2);
			set_ctx($gl2); concat_seq($gl, $gr, $gl2, $gr2);
		}
	} else {
		$gl = $gr = new Epsilon();
	}
	return $sym;
}

function parse_regex_term($sym, &$gl, &$gr) {
	$sym = parse_regex_factor($sym, $gl, $gr);
	if (in_array($sym, array(YY__QUERY,YY__QUERY_PLUS,YY__QUERY_QUERY,YY__PLUS,YY__PLUS_PLUS,YY__PLUS_QUERY,YY__STAR,YY__STAR_PLUS,YY__STAR_QUERY))) {
		switch ($sym) {
			case YY__QUERY:
				$sym = regexp2_get_sym();
				make_opt($gl, $gr);
				break;
			case YY__QUERY_PLUS:
				$sym = regexp2_get_sym();
				make_opt($gl, $gr, true);
				break;
			case YY__QUERY_QUERY:
				$sym = regexp2_get_sym();
				make_opt($gl, $gr, false);
				break;
			case YY__PLUS:
				$sym = regexp2_get_sym();
				make_iter(1, $gl, $gr);
				break;
			case YY__PLUS_PLUS:
				$sym = regexp2_get_sym();
				make_iter(1, $gl, $gr, true);
				break;
			case YY__PLUS_QUERY:
				$sym = regexp2_get_sym();
				make_iter(1, $gl, $gr, false);
				break;
			case YY__STAR:
				$sym = regexp2_get_sym();
				make_iter(0, $gl, $gr);
				break;
			case YY__STAR_PLUS:
				$sym = regexp2_get_sym();
				make_iter(0, $gl, $gr, true);
				break;
			case YY__STAR_QUERY:
				$sym = regexp2_get_sym();
				make_iter(0, $gl, $gr, false);
				break;
		}
	}
	return $sym;
}

function parse_regex_factor($sym, &$gl, &$gr) {
	if ($sym == YY__POINT) {
		$sym = regexp2_get_sym();
		$gl = $gr = new Charset(true, array());
	} else if ($sym == YY_ESCAPE_CHAR || $sym == YY_ESCAPE_CODE || $sym == YY_SINGLE_CHAR) {
		$sym = parse_regex_char($sym, $ch);
		$gl = $gr = new Character($ch);
	} else if ($sym == YY__LBRACK) {
		$sym = regexp2_get_sym();
		$neg = false;
		if ($sym == YY__UPARROW) {
			$sym = regexp2_get_sym();
			$neg = true;
		}
		$sym = parse_regex_char_class($sym, $set);
		if ($sym != YY__RBRACK) {
			error("']' expected, got '{$GLOBALS['sym_name'][$sym]}'");
		}
		$sym = regexp2_get_sym();
		$gl = $gr = new Charset($neg, $set);
	} else if ($sym == YY__LPAREN) {
		$sym = regexp2_get_sym();
		$sym = parse_regex($sym, $gl, $gr);
		if ($sym != YY__RPAREN) {
			error("')' expected, got '{$GLOBALS['sym_name'][$sym]}'");
		}
		$sym = regexp2_get_sym();
	}
	return $sym;
}

function parse_regex_char($sym, &$ch) {
	if ($sym == YY_ESCAPE_CHAR) {
		$sym = parse_escape_char($sym, $ch);
	} else if ($sym == YY_ESCAPE_CODE) {
		$sym = parse_escape_code($sym, $ch);
	} else if ($sym == YY_SINGLE_CHAR) {
		$sym = parse_single_char($sym, $ch);
	}
	return $sym;
}

function parse_escape_char($sym, &$ch) {
	if ($sym != YY_ESCAPE_CHAR) {
		error("<escape_char> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$ch = get_text(1);
			if ($ch === 'n') $ch = "\n";
			else if ($ch === 'r') $ch = "\r";
			else if ($ch === 't') $ch = "\t";
			else if ($ch === 'v') $ch = "\v";
			else if ($ch === 'f') $ch = "\f";
	$sym = regexp2_get_sym();
	return $sym;
}

function parse_escape_code($sym, &$ch) {
	if ($sym != YY_ESCAPE_CODE) {
		error("<escape_code> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$oct = get_text(1);
			$dec = 0;
			for ($i = 0; $i < strlen($oct); $i++) {
				$dec = ($dec * 8) + ($oct[$i] - ord('0'));
			}
			$ch = chr($dec);
	$sym = regexp2_get_sym();
	return $sym;
}

function parse_single_char($sym, &$ch) {
	if ($sym != YY_SINGLE_CHAR) {
		error("<single_char> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$ch = get_text();
	$sym = regexp2_get_sym();
	return $sym;
}

function parse_regex_char_class($sym, &$set) {
	$set = array();
	do {
		$sym = parse_regex_char($sym, $ch1);
		$set[$ch1] = 1;
		if ($sym == YY__MINUS) {
			$sym = regexp2_get_sym();
			$sym = parse_regex_char($sym, $ch2);
			for ($i = ord($ch1) + 1; $i <= ord($ch2); $i++) {
						$set[chr($i)] = 1;
					}
		}
	} while ($sym == YY_ESCAPE_CHAR || $sym == YY_ESCAPE_CODE || $sym == YY_SINGLE_CHAR);
	return $sym;
}

function parse_string($sym, &$s) {
	if ($sym != YY_STRING) {
		error("<string> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
	$s = stripcslashes(get_text(1, 1));
	$sym = get_sym();
	return $sym;
}

function parse($grammar) {
	global $pos, $text;
	global $line;
	$pos = $text = 0;
	$line = 1;
	$sym = parse_grammar(get_sym(), $grammar);
	if ($sym != YY_EOF) {
		error("<EOF> expected, got '{$GLOBALS['sym_name'][$sym]}'");
	}
}

