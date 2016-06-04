import sys
from pygments import highlight
from pygments.formatters import HtmlFormatter
from pygments.lexers import get_lexer_by_name, guess_lexer

language = None
highlights = None
filename = None

i = 1
while i < len(sys.argv):
    if sys.argv[i] in ['-l', '--language']:
        language = sys.argv[i + 1]

    elif sys.argv[i] in ['-hl', '--highlights']:
        highlights = sys.argv[i + 1]

    elif sys.argv[i] in ['-fn', '--filename']:
        filename = sys.argv[i + 1]

    else:
        code = sys.argv[i]
        i -= 1

    i += 2

if highlights != None:
    highlights = highlights.split(',')
else:
    highlights = []

## Get lexer associated to the language...
if language != None:
    lexer = get_lexer_by_name( language )

## ...or try to guess
else:
    lexer = guess_lexer( code )

## Get HTML formatter
formatter = HtmlFormatter (
    linenos=False, encoding='utf-8', nowrap=True, noclasses=True,
    hl_lines=highlights,
    filename=filename
)

## Output
print highlight(code, lexer, formatter)
