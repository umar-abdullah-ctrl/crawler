# README

## crawler.php

The `crawler.php` script is designed to crawl a given URL up to a specified depth and fetch HTML content from the crawled pages. The main functionalities of the script include:

- Clearing the existing files in the specified output directory.
- Parsing the robots.txt file to identify disallowed URLs.
- Initiating a recursive crawl with a specified depth limit, fetching HTML content, and saving it in the output directory.
- Filtering URLs based on disallowed patterns from robots.txt.

### Usage:

1. Modify the `$targetUrl` variable to set the starting URL for crawling.
2. Adjust the `$depthLimit` variable to determine the depth of the crawl.
3. Run the script to start the crawling process.

---

## search.php

The `search.php` script performs a search operation on previously crawled HTML content. It calculates scores for documents based on their relevance to a given search term and presents the top results. Key functionalities include:

- Loading HTML content from previously crawled pages.
- Extracting text data from various HTML elements such as headings, paragraphs, spans, anchors, list items, table cells, labels, and buttons.
- Calculating document vectors and scores based on a search term using cosine similarity.
- Highlighting the search term in the top search results.

### Usage:

1. Modify the `$searchTerm` variable to set the desired search term.
2. Run the script to calculate document scores and display the top results with highlighted search terms.

Note: Ensure that `crawler.php` has been executed to fetch HTML content before running `search.php`.

--- 

Feel free to reach out if you have any questions or need further assistance.