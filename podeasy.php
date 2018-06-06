<?

##########################################
########### HOW TO USE PODEASY ###########
##########################################

/*
Podeasy is made to be lightweight and plug-and-play!

1. Create a MYSQL database (or use one you already have)
2. Create a MySQL user with at least SELECT permissions (or use one you already have)
3. Using phpMyAdmin, import the import-table.sql file into your database
4. Alter the information in your new table however you like!
5. Fill out the info below
6. Put this file on your server
7. Test it
8. Link to this file from iTunes and other podcast services

*/

##########################################
######### FILL OUT THE FOLLOWING #########
##########################################

### Technical ###
$host='localhost';			#Can probably stick with this
$database='podcast';		#The database your podcast's table is in
$username='user';			#The MySQL user name (not your hosting account name)
$password='password';		#The MySQL user password

date_default_timezone_set('UTC');	#Use the timezone you're basing your MySQL table off (http://php.net/manual/en/timezones.php)

$websiteAddress='https://example.com/';

### Data ###
$authorName='Author Name';
$ownerName='Owner Name';
$ownerEmail='owner.email@gmail.com';
$managingEditor='managingeditor.email@gmail.com (Editor Name)';	#Contact about the podcast's content
$webMaster='webmaster.email@gmail.com (Admin Name)';			#Contact about RSS feed problems
$copyright='Copyright 20XX Your Name. All rights reserved.';	#Your copyright notice (or CC license, or whatever)

##########################################
################## CODE ##################
##########################################

#Don't allow error reporting- it can easily break the XML file created for RSS (RSS is just an XML file).
#Can set to 1 for testing, but set back to 0 before it goes live!
error_reporting(0);

#Get database
$db=new PDO(
	'mysql:host='.$host.';
	dbname='.$database.';
	charset=utf8',
	$username,
	$password,
	[PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

#If a ?podcast-id was passed
if(!empty($_GET['podcast-id'])){
	#Create an RSS webpage for a story
	
	#Get story info from the passed URL
	$data=$db->prepare(
		'SELECT
			title,
			pub_date,
			link,
			image,
			description,
			subtitle,
			keywords
		FROM podcasts
		WHERE
			podcast_id=?
			AND pub_date<NOW()
			AND public=1
			AND track=0'
	); #track 0 holds the podcast's information
	
	if($data->execute([$_GET['podcast-id']])){
		if($podcast=$data->fetch()){
			#Parse the results as XML
			header('Content-type: application/xml');
			
			#Get the most recent pub date; that's the podcast's pub date. This can impact Podcast players and how they update.
			$data=$db->prepare(
				'SELECT
					MAX(pub_date) AS pub_date
				FROM podcasts
				WHERE
					podcast_id=?
					AND pub_date<NOW()
					AND public=1'
			);
			if($data->execute([$_GET['podcast-id']])){
				if($maxDate=$data->fetch()){
					$keywordSplit=explode('|',$podcast['keywords']);
					
					echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">'
						,'<channel>'
							,'<atom:link href="',$websiteAddress,'rss.php?podcast-id=',$_GET['podcast-id'],'" rel="self" type="application/rss+xml" />'
							,'<title>',$podcast['title'],'</title>'
							,'<pubDate>',date('r',strtotime($maxDate['pub_date'])),'</pubDate>'
							,'<link>',$websiteAddress,'</link>'
							,'<description>',$podcast['description'],'</description>'
							,'<language>en-us</language>'
							,'<webMaster>'.$webMaster.'</webMaster>'
							,'<copyright><![CDATA[',$copyright,']]></copyright>'
							,'<docs>',$websiteAddress,'</docs>'
							,'<managingEditor>',$managingEditor,'</managingEditor>'
							,'<image>'
								,'<url>',$podcast['image'],'</url>'
								,'<title>',$podcast['title'],'</title>'
								,'<link><![CDATA[',$websiteAddress,']]></link>'
							,'</image>'
							
							#iTunes specific tags
							,'<itunes:summary><![CDATA[',$podcast['description'],']]></itunes:summary>'
							,'<itunes:author>',$authorName,'</itunes:author>'
							,'<itunes:category text="',$keywordSplit[0],'"/>'
							,'<itunes:category text="',$keywordSplit[1],'"/>'
							,'<itunes:keywords>',$keywordSplit[2],'</itunes:keywords>'
							,'<itunes:image href="',$podcast['image'],'" />'
							,'<itunes:explicit>no</itunes:explicit>'
							,'<itunes:owner>'
								,'<itunes:name><![CDATA[',$ownerName,']]></itunes:name>'
								,'<itunes:email>',$ownerEmail,'</itunes:email>'
							,'</itunes:owner>' #Owner of the podcast
							,'<itunes:subtitle><![CDATA[',$podcast['subtitle'],']]></itunes:subtitle>' #Podcast subtitle
				
					;
							
							#Get and echo story part info
							$data2=$db->prepare(
								'SELECT
									title,
									pub_date,
									link,
									image,
									description,
									subtitle,
									keywords
								FROM podcasts
								WHERE
									podcast_id=?
									AND track>0
									AND pub_date<NOW()
									AND public=1
								ORDER BY track ASC'
							);
							if($data->execute([$_GET['podcast-id']])){
								while($episode = $data->fetch()){

									echo "<item>"
										,'<title>',str_pad($episode['track'],3,'0',STR_PAD_LEFT),': ',$episode['title'],'</title>' #Episode title
										,'<link>',$episode['link'],'</link>' #Episode file link
										,"<guid>{$episode['link']}</guid>" #Episode file link
										,"<pubDate>", date('r',strtotime($episode['pub_date'])), "</pubDate>"
										,"<description><![CDATA[",$episode['description']
										,$keywordSplit[3]
										,"]]></description>" #Episode description
										,"<enclosure length='",filesize(str_replace($websiteAddress,'',$episode['link'])),"' type='audio/mpeg' url='{$episode['link']}' />" #Link to the file. Length is the file's number of bytes
									
										#iTunes specific tags
										,'<itunes:image href="',$episode['image'],'" />' #Episode image
										,'<itunes:duration>',$episode['duration'],'</itunes:duration>'
										,'<itunes:explicit>no</itunes:explicit>'
										,'<itunes:keywords>',$episode['keywords'],'</itunes:keywords>'
										,'<itunes:subtitle><![CDATA[',$episode['subtitle'],']]></itunes:subtitle>'
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
			#Do nothing; it'll go as it does below!
		}
	}
}

?>
<h1>No rss feed found!</h1>
<p>Most likely:</p>
<ul>
	<li>Your URL isn't calling a podcast-id (with <em>?podcast-id=X</em>)</li>
	<li>The podcast doesn't exist</li>
	<li>The podcast isn't public yet</li>
</ul>
<p>If you're unsure, <a href="?podcast-id=1">try this link and see if it works</a>!</p>