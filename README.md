# Spell-Checking-AutoComplete-and-Snippets-to-Your-Search-Engine
Steps:

Spelling Correction: 
1. I use tika library to implement a html parser file named ExtracContent.java. This file generates my big.txt file which contains all the terms in the inverted index of the Search Engine. 
2.I downloaded the Peter Norvig’s Spelling corrector code and use a test.php file to test the correct function and thus generating my serialized_dictionary.txt, which is the dictionary of words in my news file. 
3.In my query.php file I call out the correct function and retrieve the correct word, completing the “Did you mean” correction part.

Autocomplete: 
1. I first made the changes required in the solrconfig.xml as mentioned in the tutorials given under AutocompleteInSolr_V2.pdf 2. I use Jquery Autocomplete API to implement all this part, including ajax request, and other functions for the UI part.
3. My code will only take the last word as all the previous words are already corrected.

Snippet: 
1. I have used simple_html_dom parser to generate the plaintext out of the html pages given to us. 
2. Then I use a regex to globally match the query. And get the first result of matches. 
3. I use regex to find the key words in query and highlight them in red color by preg_replace function.
