# Recursive-descent parser and scanner generator

This is an experimental programming language front-end generator. Its implementation
is mainly based on ideas taken from [Coco/R](http://www.ssw.uni-linz.ac.at/Research/Projects/Coco/)
and [ANTLR](https://www.antlr.org/). It generates light-weight efficient scanners
and recursive-descent parsers, using LL(*) and syntatic and semantic predicates
for LL conflicts resolution.

The implementation is written as a prototype in PHP, and may generate front-ends in
C and PHP.

``` bash
$ php llk.php [options] <gramma-definition-file>
```

This project is experimental and may be incomplete.
It was written for personal usage and is not going to be actively maintained
by the author.
