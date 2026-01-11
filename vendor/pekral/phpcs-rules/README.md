# phpcs-rules
[![Latest Version](https://img.shields.io/packagist/v/pekral/phpcs-rules.svg?style=flat-square)](https://packagist.org/packages/pekral/phpcs-rules)
[![License](https://img.shields.io/github/license/pekral/phpcs-rules.svg?style=flat-square)](LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/pekral/phpcs-rules.svg?style=flat-square)](https://packagist.org/packages/pekral/phpcs-rules)

---

## 🚀 Introduction

**phpcs-rules** is an extensible package of custom rules for [PHP_CodeSniffer (PHPCS)](https://github.com/squizlabs/PHP_CodeSniffer), based on the [Slevomat Coding Standard](https://github.com/slevomat/coding-standard). It helps you maintain consistent code style and high code quality in your PHP projects.

---

## 📦 Installation

```bash
composer require --dev pekral/phpcs-rules
```

---

## ⚙️ Usage

1. Add a `ruleset.xml` file to your project or use the one provided in this package.
2. Run PHPCS with this ruleset:

```bash
vendor/bin/phpcs --standard=vendor/pekral/phpcs-rules/ruleset.xml src/
```

---

## 📝 Usage Examples

### Code check
```bash
vendor/bin/phpcs --standard=vendor/pekral/phpcs-rules/ruleset.xml src/
```

### Automatic fix
```bash
vendor/bin/phpcbf --standard=vendor/pekral/phpcs-rules/ruleset.xml src/
```

### Example configuration (ruleset.xml)
```xml
<?xml version="1.0"?>
<ruleset name="Custom PHPCS Rules">
    <rule ref="vendor/pekral/phpcs-rules/ruleset.xml"/>
    <!-- Your custom rules here -->
</ruleset>
```

---

## ⚙️ Configuration

- Rules can be extended and customized in `ruleset.xml`.
- Supports PHP 8.4+.
- Easy integration with CI/CD (GitHub Actions, GitLab CI, ...).

---

## 📋 Used Rules

This package includes the following coding standard rules:

### Arrays
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys` | Ensures array keys are sorted alphabetically |
| `SlevomatCodingStandard.Arrays.ArrayAccess` | Enforces consistent array access syntax |
| `SlevomatCodingStandard.Arrays.DisallowImplicitArrayCreation` | Prevents implicit array creation |
| `SlevomatCodingStandard.Arrays.DisallowPartiallyKeyed` | Disallows partially keyed arrays |
| `SlevomatCodingStandard.Arrays.MultiLineArrayEndBracketPlacement` | Controls multi-line array bracket placement |
| `SlevomatCodingStandard.Arrays.SingleLineArrayWhitespace` | Enforces whitespace rules for single-line arrays |
| `SlevomatCodingStandard.Arrays.TrailingArrayComma` | Requires trailing commas in multi-line arrays |

### Attributes
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Attributes.AttributeAndTargetSpacing` | Controls spacing around attributes and targets |
| `SlevomatCodingStandard.Attributes.AttributesOrder` | Enforces alphabetical ordering of attributes |
| `SlevomatCodingStandard.Attributes.RequireAttributeAfterDocComment` | Requires attributes to be placed after doc comments |

### Classes
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Classes.BackedEnumTypeSpacing` | Controls spacing in backed enums |
| `SlevomatCodingStandard.Classes.ClassConstantVisibility` | Enforces visibility declarations for class constants |
| `SlevomatCodingStandard.Classes.ClassLength` | Limits class length (max 500 lines, excludes tests) |
| `SlevomatCodingStandard.Classes.ClassMemberSpacing` | Controls spacing between class members |
| `SlevomatCodingStandard.Classes.ClassStructure` | Enforces class structure rules |
| `SlevomatCodingStandard.Classes.ConstantSpacing` | Controls spacing around constants |
| `SlevomatCodingStandard.Classes.DisallowLateStaticBindingForConstants` | Prevents late static binding for constants |
| `SlevomatCodingStandard.Classes.EmptyLinesAroundClassBraces` | Controls empty lines around class braces |
| `SlevomatCodingStandard.Classes.EnumCaseSpacing` | Controls spacing in enum cases |
| `SlevomatCodingStandard.Classes.ForbiddenPublicProperty` | Disallows public properties |
| `SlevomatCodingStandard.Classes.MethodSpacing` | Controls spacing between methods |
| `SlevomatCodingStandard.Classes.ModernClassNameReference` | Enforces modern class name references |
| `SlevomatCodingStandard.Classes.ParentCallSpacing` | Controls spacing around parent calls |
| `SlevomatCodingStandard.Classes.PropertyDeclaration` | Enforces property declaration rules |
| `SlevomatCodingStandard.Classes.PropertySpacing` | Controls spacing around properties |
| `SlevomatCodingStandard.Classes.RequireAbstractOrFinal` | Requires classes to be abstract or final |
| `SlevomatCodingStandard.Classes.RequireConstructorPropertyPromotion` | Enforces constructor property promotion |
| `SlevomatCodingStandard.Classes.RequireMultiLineMethodSignature` | Requires multi-line method signatures for long lines |
| `SlevomatCodingStandard.Classes.RequireSelfReference` | Enforces self references |
| `SlevomatCodingStandard.Classes.RequireSingleLineMethodSignature` | Requires single-line method signatures for short lines |
| `SlevomatCodingStandard.Classes.SuperfluousAbstractClassNaming` | Prevents superfluous abstract class naming |
| `SlevomatCodingStandard.Classes.SuperfluousErrorNaming` | Prevents superfluous error naming |
| `SlevomatCodingStandard.Classes.SuperfluousExceptionNaming` | Prevents superfluous exception naming |
| `SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming` | Prevents superfluous interface naming |
| `SlevomatCodingStandard.Classes.SuperfluousTraitNaming` | Prevents superfluous trait naming |
| `SlevomatCodingStandard.Classes.TraitUseDeclaration` | Enforces trait use declaration rules |
| `SlevomatCodingStandard.Classes.TraitUseSpacing` | Controls spacing around trait uses |
| `SlevomatCodingStandard.Classes.UselessLateStaticBinding` | Prevents useless late static binding |

### Commenting
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Commenting.AnnotationName` | Enforces annotation naming conventions |
| `SlevomatCodingStandard.Commenting.DeprecatedAnnotationDeclaration` | Controls deprecated annotation declarations |
| `SlevomatCodingStandard.Commenting.DisallowCommentAfterCode` | Prevents comments after code |
| `SlevomatCodingStandard.Commenting.DisallowOneLinePropertyDocComment` | Disallows one-line property doc comments |
| `SlevomatCodingStandard.Commenting.DocCommentSpacing` | Controls doc comment spacing |
| `SlevomatCodingStandard.Commenting.EmptyComment` | Prevents empty comments |
| `SlevomatCodingStandard.Commenting.ForbiddenAnnotations` | Disallows forbidden annotations |
| `SlevomatCodingStandard.Commenting.ForbiddenComments` | Disallows forbidden comments |
| `SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration` | Enforces inline doc comment declarations |
| `SlevomatCodingStandard.Commenting.UselessFunctionDocComment` | Prevents useless function doc comments |
| `SlevomatCodingStandard.Commenting.UselessInheritDocComment` | Prevents useless inherit doc comments |

### Complexity
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Complexity.Cognitive` | Limits cognitive complexity (warning/error threshold: 9) |

### Control Structures
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.ControlStructures.AssignmentInCondition` | Prevents assignments in conditions |
| `SlevomatCodingStandard.ControlStructures.BlockControlStructureSpacing` | Controls spacing around control structures |
| `SlevomatCodingStandard.ControlStructures.DisallowEmpty` | Disallows empty control structures |
| `SlevomatCodingStandard.ControlStructures.DisallowContinueWithoutIntegerOperandInSwitch` | Prevents continue without integer operand in switch |
| `SlevomatCodingStandard.ControlStructures.DisallowTrailingMultiLineTernaryOperator` | Disallows trailing multi-line ternary operators |
| `SlevomatCodingStandard.ControlStructures.DisallowYodaComparison` | Disallows Yoda comparisons |
| `SlevomatCodingStandard.ControlStructures.EarlyExit` | Enforces early exit patterns |
| `SlevomatCodingStandard.ControlStructures.JumpStatementsSpacing` | Controls spacing around jump statements |
| `SlevomatCodingStandard.ControlStructures.LanguageConstructWithParentheses` | Requires parentheses with language constructs |
| `SlevomatCodingStandard.ControlStructures.NewWithParentheses` | Requires parentheses with new operator |
| `SlevomatCodingStandard.ControlStructures.RequireMultiLineCondition` | Requires multi-line conditions for long lines |
| `SlevomatCodingStandard.ControlStructures.RequireMultiLineTernaryOperator` | Requires multi-line ternary operators for long lines |
| `SlevomatCodingStandard.ControlStructures.RequireNullCoalesceEqualOperator` | Enforces null coalesce equal operator |
| `SlevomatCodingStandard.ControlStructures.RequireNullCoalesceOperator` | Enforces null coalesce operator |
| `SlevomatCodingStandard.ControlStructures.RequireNullSafeObjectOperator` | Enforces null safe object operator |
| `SlevomatCodingStandard.ControlStructures.RequireShortTernaryOperator` | Enforces short ternary operators |
| `SlevomatCodingStandard.ControlStructures.RequireSingleLineCondition` | Requires single-line conditions for short lines |
| `SlevomatCodingStandard.ControlStructures.RequireTernaryOperator` | Enforces ternary operators |
| `SlevomatCodingStandard.ControlStructures.UselessIfConditionWithReturn` | Prevents useless if conditions with return |
| `SlevomatCodingStandard.ControlStructures.UselessTernaryOperator` | Prevents useless ternary operators |

### Exceptions
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Exceptions.DeadCatch` | Prevents dead catch blocks |
| `SlevomatCodingStandard.Exceptions.ReferenceThrowableOnly` | Enforces Throwable references only |
| `SlevomatCodingStandard.Exceptions.RequireNonCapturingCatch` | Requires non-capturing catch blocks |

### Files
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Files.FileLength` | Limits file length (max 500 lines, excludes tests) |
| `SlevomatCodingStandard.Files.LineLength` | Limits line length (max 156 chars, ignores comments/imports) |

### Functions
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Functions.ArrowFunctionDeclaration` | Enforces arrow function declarations |
| `SlevomatCodingStandard.Functions.DisallowEmptyFunction` | Disallows empty functions |
| `SlevomatCodingStandard.Functions.FunctionLength` | Limits function length (max 30 lines) |
| `SlevomatCodingStandard.Functions.DisallowTrailingCommaInClosureUse` | Disallows trailing commas in closure use |
| `SlevomatCodingStandard.Functions.NamedArgumentSpacing` | Controls named argument spacing |
| `SlevomatCodingStandard.Functions.RequireArrowFunction` | Enforces arrow functions |
| `SlevomatCodingStandard.Functions.RequireMultiLineCall` | Requires multi-line calls for long lines |
| `SlevomatCodingStandard.Functions.RequireSingleLineCall` | Requires single-line calls for short lines |
| `SlevomatCodingStandard.Functions.RequireTrailingCommaInCall` | Requires trailing commas in calls |
| `SlevomatCodingStandard.Functions.RequireTrailingCommaInDeclaration` | Requires trailing commas in declarations |
| `SlevomatCodingStandard.Functions.StaticClosure` | Enforces static closures |
| `SlevomatCodingStandard.Functions.StrictCall` | Enforces strict calls |
| `SlevomatCodingStandard.Functions.UnusedInheritedVariablePassedToClosure` | Prevents unused inherited variables in closures |
| `SlevomatCodingStandard.Functions.UnusedParameter` | Prevents unused parameters |
| `SlevomatCodingStandard.Functions.UselessParameterDefaultValue` | Prevents useless parameter default values |

### Methods
| Rule | Description |
|------|-------------|
| `PSR2.Methods.FunctionClosingBrace` | Enforces PSR2 function closing brace rules |
| `PSR2.Methods.MethodDeclaration.Underscore` | Prevents underscore in method declarations |

### Namespaces
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Namespaces.AlphabeticallySortedUses` | Enforces alphabetically sorted use statements |
| `SlevomatCodingStandard.Namespaces.DisallowGroupUse` | Disallows group use statements |
| `SlevomatCodingStandard.Namespaces.FullyQualifiedClassNameInAnnotation` | Enforces fully qualified class names in annotations |
| `SlevomatCodingStandard.Namespaces.MultipleUsesPerLine` | Controls multiple uses per line |
| `SlevomatCodingStandard.Namespaces.NamespaceDeclaration` | Enforces namespace declaration rules |
| `SlevomatCodingStandard.Namespaces.NamespaceSpacing` | Controls namespace spacing |
| `SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly` | Enforces reference of used names only |
| `SlevomatCodingStandard.Namespaces.RequireOneNamespaceInFile` | Requires one namespace per file |
| `SlevomatCodingStandard.Namespaces.UnusedUses` | Prevents unused use statements |
| `SlevomatCodingStandard.Namespaces.UseDoesNotStartWithBackslash` | Prevents use statements starting with backslash |
| `SlevomatCodingStandard.Namespaces.UseFromSameNamespace` | Prevents use statements from same namespace |
| `SlevomatCodingStandard.Namespaces.UseSpacing` | Controls use statement spacing |
| `SlevomatCodingStandard.Namespaces.UselessAlias` | Prevents useless aliases |

### Naming Conventions
| Rule | Description |
|------|-------------|
| `Generic.NamingConventions.CamelCapsFunctionName` | Enforces camelCase function naming |
| `PSR2.Classes.PropertyDeclaration.Underscore` | Prevents underscore in property declarations |
| `Squiz.NamingConventions.ValidFunctionName.PrivateNoUnderscore` | Prevents underscore in private function names |
| `Squiz.NamingConventions.ValidVariableName.NotCamelCaps` | Enforces camelCase variable naming |
| `Squiz.NamingConventions.ValidVariableName.PrivateNoUnderscore` | Prevents underscore in private variable names |

### Numbers
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Numbers.RequireNumericLiteralSeparator` | Requires numeric literal separators |

### Operators
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Operators.DisallowEqualOperators` | Disallows equal operators |
| `SlevomatCodingStandard.Operators.NegationOperatorSpacing` | Controls negation operator spacing |
| `SlevomatCodingStandard.Operators.RequireCombinedAssignmentOperator` | Enforces combined assignment operators |
| `SlevomatCodingStandard.Operators.SpreadOperatorSpacing` | Controls spread operator spacing |
| `SlevomatCodingStandard.Operators.RequireOnlyStandaloneIncrementAndDecrementOperators` | Enforces standalone increment/decrement operators |

### PHP
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.PHP.DisallowDirectMagicInvokeCall` | Disallows direct magic invoke calls |
| `SlevomatCodingStandard.PHP.UselessParentheses` | Prevents useless parentheses |
| `SlevomatCodingStandard.PHP.ForbiddenClasses` | Disallows forbidden classes |
| `SlevomatCodingStandard.PHP.OptimizedFunctionsWithoutUnpacking` | Enforces optimized functions without unpacking |
| `SlevomatCodingStandard.PHP.ReferenceSpacing` | Controls reference spacing |
| `SlevomatCodingStandard.PHP.RequireExplicitAssertion` | Requires explicit assertions |
| `SlevomatCodingStandard.PHP.RequireNowdoc` | Enforces nowdoc syntax |
| `SlevomatCodingStandard.PHP.ShortList` | Enforces short list syntax |
| `SlevomatCodingStandard.PHP.TypeCast` | Enforces type casting rules |
| `SlevomatCodingStandard.PHP.UselessSemicolon` | Prevents useless semicolons |
| `SlevomatCodingStandard.PHP.DisallowReference` | Disallows references |

### Strings
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Strings.DisallowVariableParsing` | Disallows variable parsing in strings |

### Type Hints
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.TypeHints.ClassConstantTypeHint` | Enforces class constant type hints |
| `SlevomatCodingStandard.TypeHints.DeclareStrictTypes` | Enforces strict types declaration |
| `SlevomatCodingStandard.TypeHints.DisallowArrayTypeHintSyntax` | Disallows array type hint syntax |
| `SlevomatCodingStandard.TypeHints.DNFTypeHintFormat` | Enforces DNF type hint format |
| `SlevomatCodingStandard.TypeHints.LongTypeHints` | Controls long type hints |
| `SlevomatCodingStandard.TypeHints.NullableTypeForNullDefaultValue` | Enforces nullable types for null default values |
| `SlevomatCodingStandard.TypeHints.NullTypeHintOnLastPosition` | Enforces null type hints on last position |
| `SlevomatCodingStandard.TypeHints.ParameterTypeHint` | Enforces parameter type hints |
| `SlevomatCodingStandard.TypeHints.ParameterTypeHintSpacing` | Controls parameter type hint spacing |
| `SlevomatCodingStandard.TypeHints.PropertyTypeHint` | Enforces property type hints |
| `SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint` | Enforces native type hints for properties |
| `SlevomatCodingStandard.TypeHints.ReturnTypeHint` | Enforces return type hints |
| `SlevomatCodingStandard.TypeHints.ReturnTypeHintSpacing` | Controls return type hint spacing |
| `SlevomatCodingStandard.TypeHints.UselessConstantTypeHint` | Prevents useless constant type hints |

### Variables
| Rule | Description |
|------|-------------|
| `SlevomatCodingStandard.Variables.DisallowSuperGlobalVariable` | Disallows super global variables |
| `SlevomatCodingStandard.Variables.DisallowVariableVariable` | Disallows variable variables |
| `SlevomatCodingStandard.Variables.DuplicateAssignmentToVariable` | Prevents duplicate assignments to variables |
| `SlevomatCodingStandard.Variables.UnusedVariable` | Prevents unused variables |
| `SlevomatCodingStandard.Variables.UselessVariable` | Prevents useless variables |

### Whitespace and Formatting
| Rule | Description |
|------|-------------|
| `Generic.Arrays.ArrayIndent` | Controls array indentation (4 spaces) |
| `Generic.WhiteSpace.DisallowTabIndent` | Disallows tab indentation |
| `Generic.WhiteSpace.ScopeIndent` | Controls scope indentation (4 spaces) |
| `PEAR.ControlStructures.MultiLineCondition` | Controls multi-line condition indentation (4 spaces) |
| `PEAR.WhiteSpace.ObjectOperatorIndent` | Controls object operator indentation (4 spaces) |
| `SlevomatCodingStandard.Whitespaces.DuplicateSpaces` | Prevents duplicate spaces |
| `Squiz.Strings.ConcatenationSpacing` | Controls concatenation spacing (1 space) |
| `Squiz.WhiteSpace.OperatorSpacing` | Controls operator spacing |

### External Rules
| Rule | Description |
|------|-------------|
| `Squiz.PHP.DiscouragedFunctions` | Disallows discouraged functions (dump, dd, error_log, var_dump, die) |

---

## ❓ FAQ

**Q: How do I add a custom rule?**
A: Add it to your `ruleset.xml` or extend this package.

**Q: How do I run PHPCS only on specific folders?**
A: Adjust the path in the PHPCS command, e.g. `src/`, `app/`.

**Q: How can I contribute?**
A: Open an issue or pull request on GitHub.

---

## 🔗 Further Resources

- [PHP_CodeSniffer (PHPCS)](https://github.com/squizlabs/PHP_CodeSniffer)
- [Slevomat Coding Standard](https://github.com/slevomat/coding-standard)
---

## 📝 License

This package is licensed under the [MIT license](LICENSE).