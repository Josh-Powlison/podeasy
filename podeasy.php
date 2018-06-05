<?

##########################################
########### HOW TO USE PODEASY ###########
##########################################

/*
Podeasy is made to be lightweight and plug-and-play!

1. Create a MYSQL Database (or use one you already have)
2. Using phpMyAdmin, load in the table-layout.sql file
3. Alter the information in your new table however you like
4. Fill out the info below
5. Put this file on your server
6. Link to this file from iTunes and other podcast services

*/

##########################################
######### FILL OUT THE FOLLOWING #########
##########################################

### Technical ###
$host='localhost';												#Can probably stick with this
$database='podcast';											#The database your podcast's table is in
$username='user';												#The MySQL user name (not your hosting account name)
$password='password';											#The MySQL user password

date_default_timezone_set('UTC');								#Use the timezone you're basing your MySQL table off (http://php.net/manual/en/timezones.php)

### Data ###
$webMaster='email@gmail.com (Admin Name)';						#People can contact them if something goes wrong with your RSS feed
$managingEditor='email@gmail.com (Editor Name)';				#People can contact them with questions or comments on the podcast
$copyright='Copyright 20XX Your Name. All rights reserved.';	#Your copyright notice (or lack of copyright notice)

##########################################
################## CODE ##################
##########################################

#Echo out an RSS feed

#Don't allow error reporting- it can easily break the XML file created for RSS (RSS is just an XML file).
error_reporting(0);

#Get database
$db=new PDO(
	'mysql:host='.$host.';
	dbname='.$podcast.';
	charset=utf8',
	$username,
	$password,
	[PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

#If a story was passed
if(
	!empty($_GET['podcast-id']) #If the basename is blank
){
	#Create an RSS webpage for a story
	
	#Get story info from the passed URL
	$data=$db->prepare("SELECT *
		FROM podcasts
		WHERE podcast_id=?
		AND track=0"); #track 0 holds the podcast's information
	
	if($data->execute([$_GET['podcast-id']])){
		if($row=$data->fetch()){
			#Parse the results as XML
			header("Content-type: application/xml");
			
			#Get the most recent pub date; that's the podcast's pub date
			$dataMid=$db->prepare("SELECT MAX(pub_date) AS pub_date
				FROM podcasts
				WHERE podcast_id=?
				AND pub_date<NOW()
				AND public=1");
			if($dataMid->execute([$_GET['podcast-id']])){
				if($rowMid=$dataMid->fetch()){
					$keywordSplit=explode('|',$row['keywords']);
					
					echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">'
						,'<channel>'
							,'<atom:link href="https://joshpowlison.com/rss.php?podcast-id=',$_GET['podcast-id'],'" rel="self" type="application/rss+xml" />'
							,'<title>',$row['title'],'</title>'
							,'<pubDate>',date('r',strtotime($rowMid['pub_date'])),'</pubDate>'
							,'<link>https://joshpowlison.com/</link>'
							,'<description>',$row["description"],'</description>'
							,'<language>en-us</language>'
							,'<webMaster>'.$webMaster.'</webMaster>'
							,'<copyright><![CDATA[',$copyright,']]></copyright>'
							,'<docs>https://joshpowlison.com/</docs>'
							,'<managingEditor>',$managingEditor,'</managingEditor>'
							,'<image>'
								,'<url>',$row['image'],'</url>'
								,'<title>',$row['title'],'</title>'
								,'<link><![CDATA[https://joshpowlison.com/]]></link>'
							,'</image>'
							
							#iTunes specific tags
							,'<itunes:summary><![CDATA[',$row['description'],']]></itunes:summary>'
							,'<itunes:author>Josh Powlison</itunes:author>'
							,'<itunes:category text="',$keywordSplit[0],'"/>'
							,'<itunes:category text="',$keywordSplit[1],'"/>'
							,'<itunes:keywords>',$keywordSplit[2],'</itunes:keywords>'
							,'<itunes:image href="',$row['image'],'" />'
							,'<itunes:explicit>no</itunes:explicit>'
							,'<itunes:owner>'
								,'<itunes:name><![CDATA[Josh Powlison]]></itunes:name>'
								,'<itunes:email>joshuapowlison@gmail.com</itunes:email>'
							,'</itunes:owner>' #Owner of the podcast
							,'<itunes:subtitle><![CDATA[',$row['subtitle'],']]></itunes:subtitle>' #Podcast subtitle
				
					;
							
							#Get and echo story part info
							$data2=$db->prepare("SELECT *
								FROM podcasts
								WHERE podcast_id=?
								AND track>0
								AND pub_date<NOW()
								AND public=1
								ORDER BY track ASC");
							if($data2->execute([$_GET['podcast-id']])){
								while($row2 = $data2->fetch()){

									echo "<item>"
										,'<title>',str_pad($row2['track'],3,'0',STR_PAD_LEFT),': ',$row2['title'],'</title>' #Episode title
										,'<link>',$row2['link'],'</link>' #Episode file link
										,"<guid>{$row2['link']}</guid>" #Episode file link
										,"<pubDate>", date('r',strtotime($row2['pub_date'])), "</pubDate>"
										,"<description><![CDATA[",$row2['description']
										,$keywordSplit[3]
										,"]]></description>" #Episode description
										,"<enclosure length='",filesize(str_replace('http://joshpowlison.com/','',$row2['link'])),"' type='audio/mpeg' url='{$row2['link']}' />" #Link to the file. Length is the file's number of bytes
									
										#iTunes specific tags
										,'<itunes:image href="',$row2['image'],'" />' #Episode image
										,'<itunes:duration>',$row2['duration'],'</itunes:duration>'
										,'<itunes:explicit>no</itunes:explicit>'
										,'<itunes:keywords>',$row2['keywords'],'</itunes:keywords>'
										,'<itunes:subtitle><![CDATA[',$row2['subtitle'],']]></itunes:subtitle>'
									,'</item>';
								}
							}

					echo '</channel>
					</rss>';

					#Don't show the below text if a story was set
					exit;
				}
			}
		}else{ #The rss feed wasn't found
			echo "We couldn't find the rss feed you called for!";
			exit;
		}
	}
}

?>No rss feed found!