# quotes-master
A wordpress plugin for managing quotes, authors, quotes topics on your websites.

Install this plugin and start enjoying quotes module on your website. You can add quotes, authors and topics from admin also bulk upload feature is there to make your work easy.

# Usage

## Get All Quotes by Author
`[getquoteforauthor slug="authorslug" page="pageno" limit="perpagelimit"]`

Above you can get all the quotes of one author by providing author slug in slug parameter. Also use page and limit parametres to get quotes in pagination.

## Get All Quotes by Topics
`[getquotefortopic slug="topicslug" page="pageno" limit="perpagelimit"]`

Above you can get all the quotes of one topic by providing topic slug in slug parameter. Also as per author use page and limit parametres to get quotes in pagination.

## How to Show Quotes

Example to fetch all quotes by topics

```
$allQuotesByTopics = json_decode(do_shortcode("[getquotefortopic slug="topicslug" page="pageno" limit="perpagelimit"]"));
if($allQuotesByTopics->quotes){
    $allQuotesByTopics->quotes = json_decode($allQuotesByTopics->quotes);
};
if($allQuotesByTopics->allTopics){
    $allQuotesByTopics->allTopics = json_decode($allQuotesByTopics->allTopics);
};
```

Example to fetch all quotes by author

```
$allQuotesByAuthor = json_decode(do_shortcode("[getquoteforauthor slug="authorslug" page="pageno" limit="perpagelimit"]"));
if($allQuotesByAuthor->quotes){
    $allQuotesByAuthor->quotes = json_decode($allQuotesByAuthor->quotes);
};
if($allQuotesByAuthor->allTopics){
    $allQuotesByAuthor->allTopics = json_decode($allQuotesByAuthor->allTopics);
};
```
