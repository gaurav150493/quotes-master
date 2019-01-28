function inactiveQuote(id){
    var url = window.location.origin+window.location.pathname+'?page=quotes&action=inactive&id='+id;
    window.location.href = url;
}

function deleteAuthor(id){
    if(confirm('Are you sure you want to delete this author?')){
        var url = window.location.origin+window.location.pathname+'?page=authors&action=delete&id='+id;
        window.location.href = url;
    };
}

function activeQuote(id){
    var url = window.location.origin+window.location.pathname+'?page=quotes&action=active&id='+id;
    window.location.href = url;
}

function showAddTopic(){
    document.getElementsByClassName('add_new_topic_popup')[0].style.display = 'block';
}

function addnewtopic(form, e){
    e.preventDefault();
    document.getElementById('topicErr').style.display = 'none';
    var ajaxurl = currentUrl+'/addtopic.php';
    var newtopic = form.elements[0].value;
    if(newtopic){
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {newtopic: newtopic}
        }).done(function(data) {
            if(data=='topic added'){
                document.getElementById('topicForm').style.display = 'none';
                document.getElementsByClassName('topic_success')[0].style.display = 'block';
                setTimeout(() => {
                    document.getElementsByClassName('topic_success')[0].style.display = 'none';
                    document.getElementById('topicForm').style.display = 'block';
                    document.getElementsByClassName('add_new_topic_popup')[0].style.display = 'none';
                    jQuery('#topicForm .form-field [name="newtopic"]').val('');
                    fetchAllQuotes();
                }, 1000);
            } else {
                alert('Something went wrong. Please try again');
                console.log(data);
            };
        }).error(function(err){
            alert('Something went wrong. Please try again');
            console.log(data);
        });
    } else {
        document.getElementById('topicErr').style.display = 'block';
    };
}

function closeTopicBox(){
    document.getElementsByClassName('add_new_topic_popup')[0].style.display = 'none';
}

function fetchAllQuotes(){
    var ajaxurl = currentUrl+'/getalltopics.php';
    jQuery.ajax({
        type: 'GET',
        url: ajaxurl
    }).done(function(data) {
        data = JSON.parse(data);
        if(data.status==1){
            var alltopics = data.data;
            var allTopicsHtml = '<option value="">Select Topics</option>';
            for(var i=0; i<alltopics.length; i++){
                allTopicsHtml += '<option value="'+alltopics[i]['id']+'">'+alltopics[i]['name']+'</option>';
            };
            jQuery('#alltopicsselect').html(allTopicsHtml);
        } else {
            alert('Something went wrong. Please try again');
        };
    }).error(function(err){
        alert('Something went wrong. Please try again');
        console.log(data);
    });
}
jQuery(document).ready(function(){
    jQuery('#adob').datepicker({
        dateFormat: 'yy-mm-dd'
    });
    jQuery('#adod').datepicker({
        dateFormat: 'yy-mm-dd'
    });
});
