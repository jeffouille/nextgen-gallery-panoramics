<?php
		/**
		 * Stops the script including a JS file more than once.  wp_enqueue_script only works
		 * before any buffers have been outputted, so this will have to do
		 * @param string $filename The path/url to the js file to be included
		 * @author Shaun <shaun@worldwidecreative.co.za>
		 * @return string with the <script> tags if not included already, else nothing
		 */
		function nggv_include_js($filename) {
			global $nggv_front_scripts;
			
			if(!$nggv_front_scripts) {
				$nggv_front_scripts = array();
			}
			
			if(!$nggv_front_scripts[$filename]) {
				$nggv_front_scripts[$filename] = array('filename'=>$nggv_front_scripts[$filename], 'added'=>true);
				return '<script type="text/javascript" src="'.$filename.'"></script>';
			}
		}
	
		add_filter("ngg_show_gallery_content", "nggv_show_gallery", 10, 2);
		/**
		 * The function that display to voting form, or results depending on if a user can vote or note
		 * @param string $out The entire markup of the gallery passed from NextGEN
		 * @param int $gid The NextGEN Gallery ID
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return string The voting form (or results) appended to the original gallery markup given
		 */
		function nggv_show_gallery($out, $gid) {
			return $out.nggc_voteForm($gid, $buffer);
		}
		
		/**
		 * Using nggv_canVote() display the voting form, or results, or thank you message.  Also calls the nggv_saveVote() once a user casts their vote 
		 * @param int $gid The NextGEN Gallery ID
		 * @author Shaun <shaunalberts@gmail.com>
		 * @return string The voting form, or results, or thank you message markup
		 */
		function nggc_voteForm($gid) {
			if(!is_numeric($gid)) {
				//trigger_error("Invalid argument 1 for function ".__FUNCTION__."(\$galId).", E_USER_WARNING);
				return;
			}
			
			$options = nggv_getVotingOptions($gid);
			$out = "";
			$errOut = "";
			
			if($_POST && !$_POST["nggv"]["vote_pid_id"]) { //select box voting
				if(($msg = nggv_saveVote(array("gid"=>$gid, "vote"=>$_POST["nggv"]["vote"]))) === true) {
					$saved = true;
				}else{
					//$errOut .= '<div class="nggv-error">';
					if($msg == "VOTING NOT ENABLED") {
						$errOut .= "This gallery has not turned on voting.";
					}else if($msg == "NOT LOGGED IN") {
						$errOut .= "You need to be logged in to vote on this gallery.";
					}else if($msg == "USER HAS VOTED") {
						$errOut .= "You have already voted on this gallery.";
					}else if($msg == "IP HAS VOTED") {
						$errOut .= "This IP has already voted on this gallery.";
					}else{
						$errOut .= "There was a problem saving your vote, please try again in a few moments.";
					}
					//$errOut .= '</div>';
					//maybe return $errOut here?  user really should only get here if they are 'hacking' the dom anyway?
				}
			}else if($_GET["gid"] && is_numeric($_GET["r"])) { //star or like/dislike, js disabled
				if($options->voting_type == 3) { //like/dislike
					if($_GET['r']) {$_GET['r'] = 100;} //like/dislike is all or nothing :)
				}
				if(($msg = nggv_saveVote(array("gid"=>$gid, "vote"=>$_GET["r"]))) === true) {
					$saved = true;
				}else{
					//$errOut .= '<div class="nggv-error">';
					if($msg == "VOTING NOT ENABLED") {
						$errOut .= "This gallery has not turned on voting.";
					}else if($msg == "NOT LOGGED IN") {
						$errOut .= "You need to be logged in to vote on this gallery.";
					}else if($msg == "USER HAS VOTED") {
						$errOut .= "You have already voted on this gallery.";
					}else if($msg == "IP HAS VOTED") {
						$errOut .= "This IP has already voted on this gallery.";
					}else{
						$errOut .= "There was a problem saving your vote, please try again in a few moments.";
					}
					//$errOut .= '</div>';
					//maybe return $errOut here?  user really should only get here if they are 'hacking' the dom anyway?
				}
			}

			if($_GET['ajaxify'] && $_GET['gid'] == $gid) {
				$out .= "<!--#NGGV START AJAX RESPONSE#-->";
				$out .= "var nggv_js = {};";
				$out .= "nggv_js.options = {};";
				foreach ($options as $key=>$val) {
					$out .= 'nggv_js.options.'.$key.' = "'.$val.'";';
				}
				
				$out .= "nggv_js.saved = ".($saved ? "1" : "0").";";
				$out .= "nggv_js.msg = '".addslashes($errOut)."';";
			}else if($_GET['gid']){
				$out .= '<div class="nggv-error">';
				$out .= $errOut;
				$out .= '</div>';
			}
			
			if((($canVote = nggv_canVote($gid)) === true) && !$saved) { //they can vote, show the form
				$url = $_SERVER["REQUEST_URI"];
				$url .= (strpos($url, "?") === false ? "?" : (substr($url, -1) == "&" ? "" : "&")); //make sure the url ends in "?" or "&" correctly
				//todo, try not duplicate the GET[gid] and GET[r] if clicked 2x
				
				if($options->voting_type == 3) { //like / dislike (new from 1.5)
					$dirName = plugin_basename(dirname(__FILE__));
					$out .= nggv_include_js(WP_PLUGIN_URL.'/'.$dirName.'/js/ajaxify-likes.js');	//ajaxify voting, from v1.7
					
					$out .= '<div class="nggv_container">';
					$out .= '<a href="'.$url.'gid='.$gid.'&r=1" class="nggv-link-like"><img src="'.WP_PLUGIN_URL."/".$dirName."/images/thumbs_up.png".'" alt="Like" /></a>';
					$out .= '<a href="'.$url.'gid='.$gid.'&r=0" class="nggv-link-dislike"><img src="'.WP_PLUGIN_URL."/".$dirName."/images/thumbs_down.png".'" alt="Dislike" /></a>';
					$out .= '<img class="nggv-star-loader" src="'.WP_PLUGIN_URL.'/'.$dirName.'/images/loading.gif'.'" style="display:none;" />';
					if($options->user_results) {
						$results = nggv_getVotingResults($gid, array("likes"=>true, "dislikes"=>true));
						$out .= '<div class="like-results">';
						$out .= $results['likes'].' ';
						$out .= $results['likes'] == 1 ? 'Like, ' : 'Likes, ';
						$out .= $results['dislikes'].' ';
						$out .= $results['dislikes'] == 1 ? 'Dislike' : 'Dislikes';
						$out .= '</div>';
					}
					$out .= '</div>';
				}elseif($options->voting_type == 2) { //star
					$out .= nggv_include_js(WP_PLUGIN_URL.'/nextgen-gallery-voting/js/ajaxify-stars.js');	//ajaxify voting, from v1.7
					
					$results = nggv_getVotingResults($gid, array("avg"=>true));
					$out .= '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/nextgen-gallery-voting/css/star_rating.css" type="text/css" media="screen" />';
					$out .= '<div class="nggv_container">';
					$out .= '<span class="inline-rating">';
					$out .= '<ul class="star-rating">';
					if($options->user_results) { //user can see curent rating
						$out .= '<li class="current-rating" style="width:'.round($results["avg"]).'%;">Currently '.round($results["avg"] / 20, 1).'/5 Stars.</li>';
					}
					$out .= '<li><a href="'.$url.'gid='.$gid.'&r=20" title="1 star out of 5" class="one-star">1</a></li>';
					$out .= '<li><a href="'.$url.'gid='.$gid.'&r=40" title="2 stars out of 5" class="two-stars">2</a></li>';
					$out .= '<li><a href="'.$url.'gid='.$gid.'&r=60" title="3 stars out of 5" class="three-stars">3</a></li>';
					$out .= '<li><a href="'.$url.'gid='.$gid.'&r=80" title="4 stars out of 5" class="four-stars">4</a></li>';
					$out .= '<li><a href="'.$url.'gid='.$gid.'&r=100" title="5 stars out of 5" class="five-stars">5</a></li>';
					$out .= '</ul>';
					$out .= '</span>';
					$out .= '<img class="nggv-star-loader" src="'.WP_PLUGIN_URL."/nextgen-gallery-voting/images/loading.gif".'" style="display:none;" />';
					$out .= '</div>';
				}else{ //it will be 1, but why not use a catch all :) (drop down)
					$out .= '<div class="nggv_container">';
					$out .= '<form method="post" action="">';
					$out .= '<label forid="nggv_rating">Rate this gallery:</label>';
					$out .= '<select id="nggv_rating" name="nggv[vote]">';
					$out .= '<option value="0">0</option>';
					$out .= '<option value="10">1</option>';
					$out .= '<option value="20">2</option>';
					$out .= '<option value="30">3</option>';
					$out .= '<option value="40">4</option>';
					$out .= '<option value="50">5</option>';
					$out .= '<option value="60">6</option>';
					$out .= '<option value="70">7</option>';
					$out .= '<option value="80">8</option>';
					$out .= '<option value="90">9</option>';
					$out .= '<option value="100">10</option>';
					$out .= '</select>';
					$out .= '<input type="submit" value="Rate" />';
					$out .= '</form>';
					$out .= '</div>';
				}
			}else{ //ok, they cant vote.  what next?
				if($options->enable) { //votings enabled for this gallery, lets find out more...
					if($canVote === "NOT LOGGED IN") { //the api wants them to login to vote
						$out .= '<div class="nggv_container">';
						$out .= 'Only registered users can vote.  Please login to cast your vote';
						$out .= '</div>';
					}else if($canVote === "USER HAS VOTED" || $canVote === "IP HAS VOTED" || $canVote === true) { //api tells us they have voted, can they see results? (canVote will be true if they have just voted successfully)
						if($options->user_results) { //yes! show it
							if($options->voting_type == 3) {
								$results = nggv_getVotingResults($gid, array("likes"=>true, "dislikes"=>true));
								
								$buffer = '';
								$bufferInner = ''; //buffer the innser, so we can pass it back to the ajax request if enabled
								
								$buffer .= '<div class="nggv_container">';
								$bufferInner .= $results['likes'].' ';
								$bufferInner .= $results['likes'] == 1 ? 'Like, ' : 'Likes, ';
								$bufferInner .= $results['dislikes'].' ';
								$bufferInner .= $results['dislikes'] == 1 ? 'Dislike' : 'Dislikes';
								$buffer .= $bufferInner;
								$buffer .= '</div>';
								
								if($_GET['ajaxify']) {
									$out .= "nggv_js.nggv_container = '".addslashes($bufferInner)."';";
								}else{
									$out .= $buffer;
								}
							}elseif($options->voting_type == 2) {
								$results = nggv_getVotingResults($gid, array("avg"=>true));
								
								$buffer = '';
								$bufferInner = ''; //buffer the innser, so we can pass it back to the ajax request if enabled
								
								$buffer .= '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/nextgen-gallery-voting/css/star_rating.css" type="text/css" media="screen" />';
								$buffer .= '<div class="nggv_container">';
								$bufferInner .= '<span class="inline-rating">';
								$bufferInner .= '<ul class="star-rating">';
								$bufferInner .= '<li class="current-rating" style="width:'.round($results["avg"]).'%;">Currently '.round($results["avg"] / 20, 1).'/5 Stars.</li>';
								$bufferInner .= '<li>1</li>';
								$bufferInner .= '<li>2</li>';
								$bufferInner .= '<li>3</li>';
								$bufferInner .= '<li>4</li>';
								$bufferInner .= '<li>5</li>';
								$bufferInner .= '</ul>';
								$bufferInner .= '</span>';
								$bufferInner .= '<img class="nggv-star-loader" src="'.WP_PLUGIN_URL."/nextgen-gallery-voting/images/loading.gif".'" style="display:none;" />';
								$buffer .= $bufferInner;
								$buffer .= '</div>';

								if($_GET['ajaxify']) {
									$out .= "nggv_js.nggv_container = '".addslashes($bufferInner)."';";
								}else{
									$out .= $buffer;
								}
							}else{
								$results = nggv_getVotingResults($gid, array("avg"=>true));
								$out .= '<div class="nggv_container">';
								$out .= 'Current Average: '.round(($results["avg"] / 10), 1)." / 10";
								$out .= '</div>';
							}
						}else{ //nope, but thanks for trying
							$buffer = '';
							$bufferInner = ''; //buffer the innser, so we can pass it back to the ajax request if enabled

							$buffer .= '<div class="nggv_container">';
							$bufferInner .= 'Thank you for casting your vote!';
							$buffer .= $bufferInner;
							$buffer .= '</div>';
							
							if($_GET['ajaxify']) {
								$out .= "nggv_js.nggv_container = '".addslashes($bufferInner)."';";
							}else{
								$out .= $buffer;
							}
						}
					}
				}
			}
			
			if($_GET['ajaxify'] && $_GET['gid'] == $gid) {
				$out .= "<!--#NGGV END AJAX RESPONSE#-->";
			}
			
			return $out;
		}

		function nggv_imageVoteForm($pid) {
			if(!is_numeric($pid)) {
				//trigger_error("Invalid argument 1 for function ".__FUNCTION__."(\$galId).", E_USER_WARNING);
				return;
			}
			
			$options = nggv_getImageVotingOptions($pid);
			$out = "";
			$errOut = "";
			
			if($_POST && $_POST["nggv"]["vote_pid_id"] && $pid == $_POST["nggv"]["vote_pid_id"]) { //dont try save a vote for a gallery silly (and make sure this is the right pid cause we are in a loop)
				if(($msg = nggv_saveVoteImage(array("pid"=>$pid, "vote"=>$_POST["nggv"]["vote_image"]))) === true) {
					$saved = true;
				}else{
					//$out .= '<div class="nggv-error">';
					if($msg == "VOTING NOT ENABLED") {
						$errOut .= "Voting is not enabled for this image";
					}else if($msg == "NOT LOGGED IN") {
						$errOut .= "You need to be logged in to vote on this image.";
					}else if($msg == "USER HAS VOTED") {
						$errOut .= "You have already voted.";
					}else if($msg == "IP HAS VOTED") {
						$errOut .= "This IP has already voted.";
					}else{
						$errOut .= "There was a problem saving your vote, please try again in a few moments.";
					}
					//$out .= '</div>';
					//maybe return $out here?  user really should only get here if they are 'hacking' the dom anyway?
				}
			}else if($_GET["ngg-pid"] && is_numeric($_GET["r"]) && $pid == $_GET["ngg-pid"]) { //star and like/dislike rating, js disabled
				if($options->voting_type == 3) { //like/dislike
					if($_GET['r']) {$_GET['r'] = 100;} //like/dislike is all or nothing :)
				}
				if(($msg = nggv_saveVoteImage(array("pid"=>$pid, "vote"=>$_GET["r"]))) === true) {
					$saved = true;
				}else{
					//$out .= '<div class="nggv-error">';
					if($msg == "VOTING NOT ENABLED") {
						$errOut .= "Voting is not enabled for this image";
					}else if($msg == "NOT LOGGED IN") {
						$errOut .= "You need to be logged in to vote on this image.";
					}else if($msg == "USER HAS VOTED") {
						$errOut .= "You have already voted.";
					}else if($msg == "IP HAS VOTED") {
						$errOut .= "This IP has already voted.";
					}else{
						$errOut .= "There was a problem saving your vote, please try again in a few moments.";
					}
					//$out .= '</div>';
					//maybe return $out here?  user really should only get here if they are 'hacking' the dom anyway?
				}
			}
			
			if($_GET['ajaxify'] && $_GET['ngg-pid'] == $pid) {
				$out .= "<!--#NGGV START AJAX RESPONSE#-->";
				$out .= "var nggv_js = {};";
				$out .= "nggv_js.options = {};";
				foreach ($options as $key=>$val) {
					$out .= 'nggv_js.options.'.$key.' = "'.$val.'";';
				}
				
				$out .= "nggv_js.saved = ".($saved ? "1" : "0").";";
				$out .= "nggv_js.msg = '".addslashes($errOut)."';";
			}else{
				//TODO XMAS remove color styling
				$out .= '<div class="nggv-error" style="display:'.($errOut ? 'block' : 'none').'; border:1px solid red; background:#fcc; padding:10px;">';
				$out .= $errOut;
				$out .= '</div>';
			}
			
			if((($canVote = nggv_canVoteImage($pid)) === true) && !$saved) { //they can vote, show the form
				$url = $_SERVER["REQUEST_URI"];
				
				$url .= (strpos($url, "?") === false ? "?" : (substr($url, -1) == "&" ? "" : "&")); //make sure the url ends in "?" or "&" correctly
				//todo, try not duplicate the GET[gid] and GET[r] if clicked 2x
				if($options->voting_type == 3) { //like / dislike (new in 1.5)
					$dirName = plugin_basename(dirname(__FILE__));
					$out .= nggv_include_js(WP_PLUGIN_URL.'/'.$dirName.'/js/ajaxify-likes.js');	//ajaxify voting, from v1.7
					
					$out .= '<div class="nggv_container">';
					$out .= '<a href="'.$url.'ngg-pid='.$pid.'&r=1" class="nggv-link-like"><img src="'.WP_PLUGIN_URL."/".$dirName."/images/thumbs_up.png".'" alt="Like" /></a>';
					$out .= '<a href="'.$url.'ngg-pid='.$pid.'&r=0" class="nggv-link-dislike"><img src="'.WP_PLUGIN_URL."/".$dirName."/images/thumbs_down.png".'" alt="Dislike" /></a>';
					$out .= '<img class="nggv-star-loader" src="'.WP_PLUGIN_URL.'/'.$dirName.'/images/loading.gif'.'" style="display:none;" />';
					if($options->user_results) {
						$results = nggv_getImageVotingResults($pid, array("likes"=>true, "dislikes"=>true));
						$out .= '<div class="like-results">';
						$out .= $results['likes'].' ';
						$out .= $results['likes'] == 1 ? 'Like, ' : 'Likes, ';
						$out .= $results['dislikes'].' ';
						$out .= $results['dislikes'] == 1 ? 'Dislike' : 'Dislikes';
						$out .= '</div>';
					}
					$out .= '</div>';
				}elseif($options->voting_type == 2) { //star
					$out .= nggv_include_js(WP_PLUGIN_URL.'/nextgen-gallery-voting/js/ajaxify-stars.js');	//ajaxify voting, from v1.7
					$results = nggv_getImageVotingResults($pid, array("avg"=>true));
					$out .= '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/nextgen-gallery-voting/css/star_rating.css" type="text/css" media="screen" />';
					$out .= '<div class="nggv_container">';
					$out .= '<span class="inline-rating">';
					$out .= '<ul class="star-rating">';
					if($options->user_results) { //user can see curent rating
						$out .= '<li class="current-rating" style="width:'.round($results["avg"]).'%;">Currently '.round($results["avg"] / 20, 1).'/5 Stars.</li>';
					}
					$out .= '<li><a href="'.$url.'ngg-pid='.$pid.'&r=20" title="1 star out of 5" class="one-star">1</a></li>';
					$out .= '<li><a href="'.$url.'ngg-pid='.$pid.'&r=40" title="2 stars out of 5" class="two-stars">2</a></li>';
					$out .= '<li><a href="'.$url.'ngg-pid='.$pid.'&r=60" title="3 stars out of 5" class="three-stars">3</a></li>';
					$out .= '<li><a href="'.$url.'ngg-pid='.$pid.'&r=80" title="4 stars out of 5" class="four-stars">4</a></li>';
					$out .= '<li><a href="'.$url.'ngg-pid='.$pid.'&r=100" title="5 stars out of 5" class="five-stars">5</a></li>';
					$out .= '</ul>';
					$out .= '</span>';
					$out .= '<img class="nggv-star-loader" src="'.WP_PLUGIN_URL."/nextgen-gallery-voting/images/loading.gif".'" style="display:none;" />';
					$out .= '</div>';
				}else{
					/* dev note.  you can set any values from 0-100 (the api will only allow this range) */
					$out .= '<div class="nggv-image-vote-container">';
					$out .= '<form method="post" action="">';
					$out .= '<label forid="nggv_rating_image_'.$pid.'">Rate this image:</label>';
					$out .= '<input type="hidden" name="nggv[vote_pid_id]" value="'.$pid.'" />';
					$out .= '<select id="nggv_rating_image_'.$pid.'" name="nggv[vote_image]">';
					$out .= '<option value="0">0</option>';
					$out .= '<option value="10">1</option>';
					$out .= '<option value="20">2</option>';
					$out .= '<option value="30">3</option>';
					$out .= '<option value="40">4</option>';
					$out .= '<option value="50">5</option>';
					$out .= '<option value="60">6</option>';
					$out .= '<option value="70">7</option>';
					$out .= '<option value="80">8</option>';
					$out .= '<option value="90">9</option>';
					$out .= '<option value="100">10</option>';
					$out .= '</select>';
					$out .= '<input type="submit" value="Rate" />';
					$out .= '</form>';
					$out .= '</div>';
				}
			}else{ //ok, they cant vote.  what next?
				if($options->enable) { //votings enabled for this gallery, lets find out more...
					if($canVote === "NOT LOGGED IN") { //the api wants them to login to vote
						$out .= '<div class="nggv-image-vote-container">';
						$out .= 'Only registered users can vote on this image.  Please login to cast your vote';
						$out .= '</div>';
					}else if($canVote === "USER HAS VOTED" || $canVote === "IP HAS VOTED" || $canVote === true) { //api tells us they have voted, can they see results? (canVote will be true if they have just voted successfully)
						if($options->user_results) { //yes! show it
							if($options->voting_type == 3) {
								$results = nggv_getImageVotingResults($pid, array("likes"=>true, "dislikes"=>true));
								
								$buffer = '';
								$bufferInner = ''; //buffer the innser, so we can pass it back to the ajax request if enabled
								
								$buffer .= '<div class="nggv_container">';
								$bufferInner .= $results['likes'].' ';
								$bufferInner .= $results['likes'] == 1 ? 'Like, ' : 'Likes, ';
								$bufferInner .= $results['dislikes'].' ';
								$bufferInner .= $results['dislikes'] == 1 ? 'Dislike' : 'Dislikes';
								$buffer .= $bufferInner;
								$buffer .= '</div>';
								
								if($_GET['ajaxify']) {
									$out .= "nggv_js.nggv_container = '".addslashes($bufferInner)."';";
								}else{
									$out .= $buffer;
								}
							}elseif($options->voting_type == 2) {
								$results = nggv_getImageVotingResults($pid, array("avg"=>true));
								
								$buffer = '';
								$bufferInner = '';
								
								$buffer .= '<link rel="stylesheet" href="'.WP_PLUGIN_URL.'/nextgen-gallery-voting/css/star_rating.css" type="text/css" media="screen" />';
								$buffer .= '<div class="nggv_container">';
								$bufferInner .= '<span class="inline-rating">';
								$bufferInner .= '<ul class="star-rating">';
								$bufferInner .= '<li class="current-rating" style="width:'.round($results["avg"]).'%;">Currently '.round($results["avg"] / 20, 1).'/5 Stars.</li>';
								$bufferInner .= '<li>1</li>';
								$bufferInner .= '<li>2</li>';
								$bufferInner .= '<li>3</li>';
								$bufferInner .= '<li>4</li>';
								$bufferInner .= '<li>5</li>';
								$bufferInner .= '</ul>';
								$bufferInner .= '</span>';
								$bufferInner .= '<img class="nggv-star-loader" src="'.WP_PLUGIN_URL."/nextgen-gallery-voting/images/loading.gif".'" style="display:none;" />';
								$buffer .= $bufferInner;
								$buffer .= '</div>';
								
								if($_GET['ajaxify']) {
									$out .= "nggv_js.nggv_container = '".addslashes($bufferInner)."';";
								}else{
									$out .= $buffer;
								}
							}else{
								$results = nggv_getImageVotingResults($pid, array("avg"=>true));
								$out .= '<div class="nggv-image-vote-container">';
								$out .= 'Current Average: '.round(($results["avg"] / 10), 1)." / 10";
								$out .= '</div>';
							}
						}else{ //nope, but thanks for trying
							$buffer = '';
							$bufferInner = ''; //buffer the innser, so we can pass it back to the ajax request if enabled
							
							$buffer .= '<div class="nggv_container">';
							$bufferInner .= 'Thank you for casting your vote!';
							$buffer .= $bufferInner;
							$buffer .= '</div>';
							
							if($_GET['ajaxify']) {
								$out .= "nggv_js.nggv_container = '".addslashes($bufferInner)."';";
							}else{
								$out .= $buffer;
							}
						}
					}
				}
			}
			
			if($_GET['ajaxify'] && $_GET['ngg-pid'] == $pid) {
				$out .= "<!--#NGGV END AJAX RESPONSE#-->";
			}
			
			return $out;
		}
	//}
        ?>
