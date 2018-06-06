<?php

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

### MySQL Info ###
$host='localhost';			#Can probably stick with this
$database='podcast';		#The database your podcast's table is in
$username='user';			#The MySQL user name (not your hosting account name)
$password='password';		#The MySQL user password

### Technical ###
date_default_timezone_set('UTC');	#Use the timezone you're basing your MySQL table off (http://php.net/manual/en/timezones.php)
$language='en-us';

### Data ###
$websiteAddress='https://example.com/';
$ownerName='Owner Name';
$ownerEmail='owner.email@gmail.com';
$managingEditor='managingeditor.email@gmail.com (Editor Name)';	#Contact about the podcast's content
$webMaster='webmaster.email@gmail.com (Admin Name)';			#Contact about RSS feed problems
$copyright='Copyright 20XX Your Name. All rights reserved.';	#Your copyright notice (or CC license, or whatever)

### Custom ###
$endingText='<br><a href="email@gmail.com">Email me</a>';		#Text to end each podcast episode description with
$leadingZeroes=2;												#Number of leading zeroes for episode numbers
$blockiTunes='no';

##########################################
################## CODE ##################
##########################################

#Don't allow error reporting- it can easily break the XML file created for RSS (RSS is just an XML file).
#Can set to 1 for testing, but set back to 0 before it goes live!
error_reporting(0);

#If a ?podcast-id was passed
if(!empty($_GET['podcast-id'])){
	#Get database
	$db=new PDO(
		'mysql:host='.$host.';
		dbname='.$database.';
		charset=utf8',
		$username,
		$password,
		[PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
	);
	
	#Get story info from the passed URL
	$data=$db->prepare(
		'SELECT
			track,
			title,
			author,
			explicit,
			pub_date,
			type,
			link,
			image,
			description,
			subtitle,
			duration,
			keywords
		FROM podcasts
		WHERE
			podcast_id=?
			AND pub_date<NOW()
			AND public=1
		ORDER BY track ASC'
		#MAX(pub_date) AS last_pub_date
	);
	
	if($data->execute([$_GET['podcast-id']])){
		$zeroFound=false;
		
		#Tag reference: http://podcastersroundtable.com/pm17/
		
		while($row=$data->fetch()){
			#Track 0 holds the podcast's information
			if($row['track']===0){
				#Parse the results as XML
				header('Content-type: application/xml');
				
				$keywordSplit=explode('|',$row['keywords']);
					
				echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd">
					<channel>
						<atom:link href="',$websiteAddress,'rss.php?podcast-id=',$_GET['podcast-id'],'" rel="self" type="application/rss+xml" />
						<title>',$row['title'],'</title>
						<pubDate>',date('r',strtotime($row['last_pub_date'])),'</pubDate>
						<generator>Podeasy</generator>
						<link>',$websiteAddress,'</link>
						<language>',$language,'</language>
						<copyright><![CDATA[',$copyright,']]></copyright>
						<docs>',$websiteAddress,'</docs>
						<image>
							<url>',$row['image'],'</url>
							<title>',$row['title'],'</title>
							<link><![CDATA[',$websiteAddress,']]></link>
						</image>
						<managingEditor>',$managingEditor,'</managingEditor>
						<description>',$row['description'],'</description>
						<webMaster>',$webMaster,'</webMaster>
						
						<itunes:summary><![CDATA[',$row['description'],']]></itunes:summary>
						<itunes:author>',$row['author'],'</itunes:author>
						<itunes:keywords>',htmlentities($keywordSplit[2]),'</itunes:keywords>
						<itunes:category text="',htmlentities($keywordSplit[0]),'"/>
						<itunes:category text="',htmlentities($keywordSplit[1]),'"/>
						<itunes:image href="',$row['image'],'" />
						<itunes:explicit>',$row['explicit'],'</itunes:explicit>
						<itunes:owner>
							<itunes:name><![CDATA[',$ownerName,']]></itunes:name>
							<itunes:email>',$ownerEmail,'</itunes:email>
						</itunes:owner>
						<itunes:subtitle><![CDATA[',$row['subtitle'],']]></itunes:subtitle>
						<itunes:block>',$blockiTunes,'</itunes:block>
						<itunes:type>',$row['type'],'</itunes:type>'
				;
				
				$zeroFound=true;
			}else{
				#If the podcast title wasn't found (or isn't public), exit
				if(!$zeroFound) break;
				
				echo '<item>
					<title>',str_pad($row['track'],$leadingZeroes,'0',STR_PAD_LEFT),': ',$row['title'],'</title>
					<itunes:title>',$row['title'],'</itunes:title>
					<pubDate>',date('r',strtotime($row['pub_date'])),'</pubDate>
					<guid>',$row['link'],'</guid>
					<link>',$row['link'],'</link>
					<description><![CDATA[',$row['description'],$endingText,']]></description>
					<enclosure length="',filesize(str_replace($websiteAddress,'',$row['link'])),'" type="audio/mpeg" url="',$row['link'],'" />
					<itunes:duration>',$row['duration'],'</itunes:duration>
					<itunes:image href="',$row['image'],'" />
					<itunes:explicit>',$row['explicit'],'</itunes:explicit>
					<itunes:block>',$blockiTunes,'</itunes:block>
					<itunes:keywords>',$row['keywords'],'</itunes:keywords>
					<itunes:subtitle><![CDATA[',$row['subtitle'],']]></itunes:subtitle>
					<itunes:summary><![CDATA[',$row['subtitle'],']]></itunes:summary>
					<itunes:episode>',$row['track'],'</itunes:episode>
					<itunes:episodeType>',$row['type'],'</itunes:episodeType>
					<itunes:author>',$row['author'],'</itunes:author>
				</item>';
				
				#### TAGS OMITTED
				# <content:encoded>		Full show notes.
				# <itunes:order>		Excluded because order's already dependent on date and <itunes:type>
				###
			}
		}
		
		#If got the podcast
		if($zeroFound){
			#Close the RSS feed	
			echo '</channel>
			</rss>';

			#Don't show the below text if a podcast was found
			exit;
		}
	}
}

#Display the following if no RSS feed is found
?>
<h1>No rss feed found!</h1>
<p>Most likely:</p>
<ul>
	<li>Your URL isn't calling a podcast-id (with <em>?podcast-id=X</em>)</li>
	<li>The podcast doesn't exist</li>
	<li>The podcast isn't public yet</li>
	<?php
	#	You didn't create a title for your podcast where track is 0
	#	public=0 on track 0
	#	You haven't loaded in import-table.sql to your database yet
	#	Problems with your MySQL user
	
	# Set error_reporting to 1 above in order to check!
	?>
</ul>
<p>If you're unsure, <a href="?podcast-id=1">try this link and see if it works</a>!</p>