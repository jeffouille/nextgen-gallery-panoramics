jQuery(function() {
		jQuery("#gallerydiv table.form-table").each(function(item) { //the table with gallery fields (hack to add to it with js, sorry)
				var row = this.insertRow(this.rows.length);
				var cell = row.insertCell(0);
				cell.align = "left";
				cell.vAlign = "top";
				cell.innerHTML = "Gallery Voting Options";
				
				var cell = row.insertCell(1);
				cell.colSpan = 3;
				var str = "";
				
				str += "<input type='checkbox' name='nggv[enable]' value=1 "+(nggv_enable ? "checked" : "")+" /> Enable voting for this gallery<br />";
				str += "<input type='checkbox' name='nggv[force_login]' value=1 "+(nggv_login ? "checked" : "")+" /> Only allow logged in users to vote<br />";
				str += "<input type='checkbox' name='nggv[force_once]' value=1 "+(nggv_once ? "checked" : "")+" /> Only allow 1 vote per person (IP or userid is used to stop multiple)<br />";
				str += "<input type='checkbox' name='nggv[user_results]' value=1 "+(user_results ? "checked" : "")+" /> Allow users to see results<br />";
				str += "Rating Type: <select name='nggv[voting_type]'>";
				str += "<option value='1' "+(voting_type == 1 ? "selected" : "")+">Drop Down</option>";
				str += "<option value='2' "+(voting_type == 2 ? "selected" : "")+">Star Rating</option>";
				str += "<option value='3' "+(voting_type == 3 ? "selected" : "")+">Like / Dislike</option>";
				str += "</select>";
				
				cell.innerHTML = str;

				row = this.insertRow(this.rows.length);
				cell = row.insertCell(0);
				cell.align = "left";
				cell.vAlign = "top";
				cell.innerHTML = "Current Votes";
				
				cell = row.insertCell(1);
				cell.colSpan = 3;
				if(voting_type == 3) { //likes/dislikes
					str = nggv_num_likes+' ';
					str += nggv_num_likes == 1 ? 'Like, ' : 'Likes, ';
					str += nggv_num_dislikes+' ';
					str += nggv_num_dislikes == 1 ? 'Dislike' : 'Dislikes';
					str += " <a href='#' id='nggv_more_results'>("+nggv_num_votes+" votes cast)</a>";
				}else{
					str = nggv_avg+" / 10 <a href='#' id='nggv_more_results'>("+nggv_num_votes+" votes cast)</a>";
				}
				
				cell.innerHTML = str;
				
				jQuery("a#nggv_more_results").click(function() { //button click to open more detail on the voting
						tb_show("", "#TB_inline?width=640&height=300&inlineId=nggvShowList&modal=true", false); //thick box seems to be included, so lets use it :)
						
						jQuery.get(nggv_more_url, 'gid='+nggv_gid, function(data, status) {
								if(status == 'success') {
									var start = data.indexOf("<!--#NGGV START AJAX RESPONSE#-->") + 33; //find the start of the outputting by the ajax url (stupid wordpress and poor buffering options blah blah)
									eval(data.substr(start)); //the array of voters gets echoed out at the ajax url
									if(nggv_votes_list.length > 0) {
										//todo, paginate results (pseudo even, with hidden divs etc)?
										var bgcol;
										var html = '<table style="width:100%;">';
										html += '<thead>';
										html += '<tr>';
										html += '<td><strong>Date</strong></td>';
										html += '<td><strong>Vote</strong><br /><em>(out 10)</em></td>';
										html += '<td><strong>User Name</strong><br ><em>(if logged in)</em></td>';
										html += '<td><strong>IP</strong></td>';
										html += '</tr>';
										html += '</thead>';
										html += '<tbody>';
										for(i=0; i<nggv_votes_list.length; i++) {
											bgcol = i % 2 == 0 ? "" : "#DFDFDF";
											html += '<tr style="background-color: '+bgcol+'">';
											html += '<td>'+nggv_votes_list[i][1]+'</td>';
											if(parseInt(nggv_voting_type) == 3) {
												html += '<td>'+(nggv_votes_list[i][0] == 100 ? 'Like' : 'Dislike')+'</td>';
											}else{
												html += '<td>'+(Math.round(nggv_votes_list[i][0]) / 10)+'</td>';
											}
											html += '<td>'+nggv_votes_list[i][3][1]+'</td>';
											html += '<td>'+nggv_votes_list[i][2]+'</td>';
											html += '</tr>';
										}
										html += '</tbody>';
										html += '</table>';
										
										jQuery("div#nggvShowList_content").html(html);
									}else{
										jQuery("div#nggvShowList_content").html("No votes yet for this gallery");
									}
								}else{
									jQuery("div#nggvShowList_content").html("There was a problem retrieving the list of votes, please try again in a momement.");
								}
						});
						return false; //cancel click
				});
				
				jQuery("a#nggv_more_results_close").click(function() {
						tb_remove();
						return false;
				});
				
				jQuery("a.nggv_mote_results_image").click(function() { //button click to open more detail on the voting
						var pid = parseInt(this.id.substr(24));
						tb_show("", "#TB_inline?width=640&height=300&inlineId=nggvShowList&modal=true", false); //thick box seems to be included, so lets use it :)
						
						jQuery.get(nggv_more_url, 'pid='+pid, function(data, status) {
								if(status == 'success') {
									var start = data.indexOf("<!--#NGGV START AJAX RESPONSE#-->") + 33; //find the start of the outputting by the ajax url (stupid wordpress and poor buffering options blah blah)
									eval(data.substr(start)); //the array of voters gets echoed out at the ajax url
									if(nggv_votes_list.length > 0) {
										//todo, paginate results (pseudo even, with hidden divs etc)?
										var bgcol;
										var html = '<table style="width:100%;">';
										html += '<thead>';
										html += '<tr>';
										html += '<td><strong>Date</strong></td>';
										html += '<td><strong>Vote</strong><br /><em>(out 10)</em></td>';
										html += '<td><strong>User Name</strong><br ><em>(if logged in)</em></td>';
										html += '<td><strong>IP</strong></td>';
										html += '</tr>';
										html += '</thead>';
										html += '<tbody>';
										for(i=0; i<nggv_votes_list.length; i++) {
											bgcol = i % 2 == 0 ? "" : "#DFDFDF";
											html += '<tr style="background-color: '+bgcol+'">';
											html += '<td>'+nggv_votes_list[i][1]+'</td>';
											if(parseInt(nggv_voting_type) == 3) {
												html += '<td>'+(nggv_votes_list[i][0] == 100 ? 'Like' : 'Dislike')+'</td>';
											}else{
												html += '<td>'+(Math.round(nggv_votes_list[i][0]) / 10)+'</td>';
											}
											html += '<td>'+nggv_votes_list[i][3][1]+'</td>';
											html += '<td>'+nggv_votes_list[i][2]+'</td>';
											html += '</tr>';
										}
										html += '</tbody>';
										html += '</table>';
										
										jQuery("div#nggvShowList_content").html(html);
									}else{
										jQuery("div#nggvShowList_content").html("No votes yet for this image");
									}
								}else{
									jQuery("div#nggvShowList_content").html("There was a problem retrieving the list of votes, please try again in a momement.");
								}
						});
						return false; //cancel click
				});
				
		});
});