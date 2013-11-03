<?php

$testTemplateSource2 = [
	'Posts' => [
		'title'    => 'templ@te',
		'list'     => [
			[
				'author'  => 'Joshua Morey',
				'date'    => '2013-03-14',
				'url'     => 'http://blog.jacere.net/2013/03/satr-development-finalized/',
				'title'   => 'SATR Development Finalized',
				'content' => '<p>I finally found some time to finish my SATR tiling algorithm for <a title="CloudAE" href="http://blog.jacere.net/cloudae/">CloudAE</a>.  Unlike the previous STAR algorithms, which were built around segmented tiling, this approach uses more efficient spatial indexing to eliminate temporary files and to minimize both read and write operations.  Another difference with this approach is that increasing the allowed memory usage for the tiling process will substantially improve the performance by lowering the duplicate read multiplier and taking advantage of more sequential reads.  For instance, increasing the indexing segment size from 256 MB to 1 GB will generally reduce the tiling time by 50% on magnetic media, and 30% on an SSD.</p>',
				
				'categories' => [
					['url' => 'http://blog.jacere.net/category/c/', 'name' => 'C#'],
					['url' => 'http://blog.jacere.net/category/cloudae/', 'name' => 'CloudAE'],
					['url' => 'http://blog.jacere.net/category/lidar/', 'name' => 'LiDAR'],
				],
			],
			[
				'author'  => 'Joshua Morey',
				'date'    => '2011-12-23',
				'url'     => 'http://blog.jacere.net/2011/12/tile-stitching/',
				'title'   => 'Tile Stitching',
				'content' => '<p><a href="http://blog.jacere.net/wp-content/uploads/2012/02/78m_stitching1.png"><img class="alignright size-medium wp-image-123" title="78m_stitching1" src="http://blog.jacere.net/wp-content/uploads/2012/02/78m_stitching1-300x217.png.pagespeed.ce.4wFFGpWx_k.png" alt="" width="220"/></a>I have completed a basic tile stitching algorithm for use in completing the 3D mesh view.  At this point, it only stitches equal resolutions, so it will stitch low-res tiles together and high-res tiles together, but it will leave a visible seam between tiles of different resolutions.  Obviously, that is not a difficult problem, since I am only stitching regular meshes at this point.  However, I do plan to get irregular mesh support eventually.  Currently, I have an incremental Delaunay implementation, but the C# version is much slower than the original C++, to the extent that it is not worth running.  The <a href="http://www.s-hull.org/">S-hull</a> implementation that I downloaded is much faster, but does not produce correct results.</p><p>The primary limitation of the tile-based meshing and stitching is that WPF 3D has performance limitations regarding the number of <a href="http://msdn.microsoft.com/en-us/library/system.windows.media.media3d.meshgeometry3d.aspx">MeshGeometry3D</a> instances in the scene.  For small to medium-sized files, there may be up to a few thousand tiles with the current tile-sizing algorithms.  WPF 3D performance degrades substantially when the number of mesh instances gets to be approximately 12,000.  Massive files may have far more tiles than that, and adding mesh instances for the stitching components makes the problem even worse.  I have some ideas for workarounds, but I have not yet decided if they are worth implementing since there are already so many limitations with WPF 3D and this 3D preview was never intended to be a real viewer.</p>',
				
				'categories' => [
					['url' => 'http://blog.jacere.net/category/cloudae/', 'name' => 'CloudAE'],
				],
			],
		]
	],
	'Header' => [
		'root-url' => 'http://test.jacere.net/template/index.php',
	],
	'Navigation' => [
		'nav-list' => [
			['url' => '#', 'text' => 'CloudAE'],
			['url' => '#', 'text' => 'Squid'],
			['url' => '#', 'text' => 'Snail'],
			['url' => '#', 'text' => 'Coda'],
		],
		'nav-list-global' => [
			['url' => '#', 'text' => 'Contact'],
			['url' => '#', 'text' => 'About'],
		],
	],
	'SidebarSectionRecent' => [
		'list' => [
			['url' => 'http://blog.jacere.net/2013/04/something/', 'text' => 'I wrote two lines of code yesterday'],
			['url' => 'http://blog.jacere.net/2013/03/satr-development-finalized/', 'text' => 'SATR Development Finalized'],
			['url' => 'http://blog.jacere.net/2012/12/snail-xml-parser/', 'text' => 'Snail XML Parser'],
			['url' => 'http://blog.jacere.net/2012/10/property-manager/', 'text' => 'PropertyManager'],
			['url' => 'http://blog.jacere.net/2012/10/matsu-point-mackenzie-in-laz/', 'text' => 'MatSu Point MacKenzie in LAZ'],
			['url' => 'http://blog.jacere.net/2012/10/point-enumeration-in-cloudae/', 'text' => 'Point Enumeration in CloudAE'],
		],
	],
	'SidebarSectionArchives' => [
		'list' => [
			['url' => 'http://blog.jacere.net/2013/03/', 'text' => '2013-03 March', 'count' => 1],
			['url' => 'http://blog.jacere.net/2012/12/', 'text' => '2012-12 December', 'count' => 1],
			['url' => 'http://blog.jacere.net/2012/10/', 'text' => '2012-10 October', 'count' => 4],
			['url' => 'http://blog.jacere.net/2012/09/', 'text' => '2012-09 September', 'count' => 1],
			['url' => 'http://blog.jacere.net/2012/06/', 'text' => '2012-06 June', 'count' => 1],
			['url' => 'http://blog.jacere.net/2012/02/', 'text' => '2012-02 February', 'count' => 1],
			['url' => 'http://blog.jacere.net/2011/12/', 'text' => '2011-12 December', 'count' => 4],
		],
	],
	'SidebarSectionCategories' => [
		'list' => [
			['url' => 'http://blog.jacere.net/category/c/', 'text' => 'C#', 'count' => 6],
			['url' => 'http://blog.jacere.net/category/cloudae/', 'text' => 'CloudAE', 'count' => 12],
			['url' => 'http://blog.jacere.net/category/lidar/', 'text' => 'LiDAR', 'count' => 1],
		],
	],
];

$testTemplateSource1 = [
	'Post' => [
		'title'    => 'templ@te',
	],
	'Header' => [
		'root-url' => 'http://test.jacere.net/template/parse.php',
	],
	'Navigation' => [
		'nav-list' => [
			['url' => '#', 'text' => 'CloudAE'],
			['url' => '#', 'text' => 'Squid'],
			['url' => '#', 'text' => 'Snail'],
			['url' => '#', 'text' => 'Coda'],
		],
		'nav-list-global' => [
			['url' => '#', 'text' => 'Contact'],
			['url' => '#', 'text' => 'About'],
		],
	],
	'SidebarSectionRecent' => [
		'list' => [
			['url' => 'http://blog.jacere.net/2013/04/something/', 'text' => 'I wrote two lines of code yesterday'],
			['url' => 'http://blog.jacere.net/2013/03/satr-development-finalized/', 'text' => 'SATR Development Finalized'],
			['url' => 'http://blog.jacere.net/2012/12/snail-xml-parser/', 'text' => 'Snail XML Parser'],
			['url' => 'http://blog.jacere.net/2012/10/property-manager/', 'text' => 'PropertyManager'],
			['url' => 'http://blog.jacere.net/2012/10/matsu-point-mackenzie-in-laz/', 'text' => 'MatSu Point MacKenzie in LAZ'],
			['url' => 'http://blog.jacere.net/2012/10/point-enumeration-in-cloudae/', 'text' => 'Point Enumeration in CloudAE'],
		],
	],
	'SidebarSectionArchives' => [
		'list' => [
			['url' => 'http://blog.jacere.net/2013/03/', 'text' => '2013-03 March', 'count' => 1],
			['url' => 'http://blog.jacere.net/2012/12/', 'text' => '2012-12 December', 'count' => 1],
			['url' => 'http://blog.jacere.net/2012/10/', 'text' => '2012-10 October', 'count' => 4],
			['url' => 'http://blog.jacere.net/2012/09/', 'text' => '2012-09 September', 'count' => 1],
			['url' => 'http://blog.jacere.net/2012/06/', 'text' => '2012-06 June', 'count' => 1],
			['url' => 'http://blog.jacere.net/2012/02/', 'text' => '2012-02 February', 'count' => 1],
			['url' => 'http://blog.jacere.net/2011/12/', 'text' => '2011-12 December', 'count' => 4],
		],
	],
	'SidebarSectionCategories' => [
		'list' => [
			['url' => 'http://blog.jacere.net/category/c/', 'text' => 'C#', 'count' => 6],
			['url' => 'http://blog.jacere.net/category/cloudae/', 'text' => 'CloudAE', 'count' => 12],
			['url' => 'http://blog.jacere.net/category/lidar/', 'text' => 'LiDAR', 'count' => 1],
		],
	],
	'PostSection' => [
		'author'  => 'Joshua Morey',
		'date'    => '2013-03-14',
		'url'     => 'http://blog.jacere.net/2013/03/satr-development-finalized/',
		'title'   => 'SATR Development Finalized',
		'content' => '<p>I finally found some time to finish my SATR tiling algorithm for <a title="CloudAE" href="http://blog.jacere.net/cloudae/">CloudAE</a>.  Unlike the previous STAR algorithms, which were built around segmented tiling, this approach uses more efficient spatial indexing to eliminate temporary files and to minimize both read and write operations.  Another difference with this approach is that increasing the allowed memory usage for the tiling process will substantially improve the performance by lowering the duplicate read multiplier and taking advantage of more sequential reads.  For instance, increasing the indexing segment size from 256 MB to 1 GB will generally reduce the tiling time by 50% on magnetic media, and 30% on an SSD.</p>',
		
		'categories' => [
			['url' => 'http://blog.jacere.net/category/c/', 'name' => 'C#'],
			['url' => 'http://blog.jacere.net/category/cloudae/', 'name' => 'CloudAE'],
			['url' => 'http://blog.jacere.net/category/lidar/', 'name' => 'LiDAR'],
		],
	]
];

$testTemplateSource3 = [
	'TenjinBench' => [
		'list' => array(
	    array(
	      'name'   => "Adobe Systems",
	      'name2'  => "Adobe Systems Inc.",
	      'symbol' => "ADBE",
	      'url'    => "http://www.adobe.com",
	      'price'  => 39.26,
	      'change' => 0.13,
	      'ratio'  => 0.33,
	      'price'  => 39.26,
	    ),
	    array(
	      'name'   => "Advanced Micro Devices",
	      'name2'  => "Advanced Micro Devices Inc.",
	      'symbol' => "AMD",
	      'url'    => "http://www.amd.com",
	      'price'  => 16.22,
	      'change' => 0.17,
	      'ratio'  => 1.06,
	      'price'  => 16.22,
	    ),
	    array(
	      'name'   => "Amazon.com",
	      'name2'  => "Amazon.com Inc",
	      'symbol' => "AMZN",
	      'url'    => "http://www.amazon.com",
	      'price'  => 36.85,
	      'change' => -0.23,
	      'ratio'  => -0.62,
	      'price'  => 36.85,
	    ),
	    array(
	      'name'   => "Apple",
	      'name2'  => "Apple Inc.",
	      'symbol' => "AAPL",
	      'url'    => "http://www.apple.com",
	      'price'  => 85.38,
	      'change' => -0.87,
	      'ratio'  => -1.01,
	      'price'  => 85.38,
	    ),
	    array(
	      'name'   => "BEA Systems",
	      'name2'  => "BEA Systems Inc.",
	      'symbol' => "BEAS",
	      'url'    => "http://www.bea.com",
	      'price'  => 12.46,
	      'change' => 0.09,
	      'ratio'  => 0.73,
	      'price'  => 12.46,
	    ),
	    array(
	      'name'   => "CA",
	      'name2'  => "CA, Inc.",
	      'symbol' => "CA",
	      'url'    => "http://www.ca.com",
	      'price'  => 24.66,
	      'change' => 0.38,
	      'ratio'  => 1.57,
	      'price'  => 24.66,
	    ),
	    array(
	      'name'   => "Cisco Systems",
	      'name2'  => "Cisco Systems Inc.",
	      'symbol' => "CSCO",
	      'url'    => "http://www.cisco.com",
	      'price'  => 26.35,
	      'change' => 0.13,
	      'ratio'  => 0.5,
	      'price'  => 26.35,
	    ),
	    array(
	      'name'   => "Dell",
	      'name2'  => "Dell Corp.",
	      'symbol' => "DELL",
	      'url'    => "http://www.dell.com/",
	      'price'  => 23.73,
	      'change' => -0.42,
	      'ratio'  => -1.74,
	      'price'  => 23.73,
	    ),
	    array(
	      'name'   => "eBay",
	      'name2'  => "eBay Inc.",
	      'symbol' => "EBAY",
	      'url'    => "http://www.ebay.com",
	      'price'  => 31.65,
	      'change' => -0.8,
	      'ratio'  => -2.47,
	      'price'  => 31.65,
	    ),
	    array(
	      'name'   => "Google",
	      'name2'  => "Google Inc.",
	      'symbol' => "GOOG",
	      'url'    => "http://www.google.com",
	      'price'  => 495.84,
	      'change' => 7.75,
	      'ratio'  => 1.59,
	      'price'  => 495.84,
	    ),
	    array(
	      'name'   => "Hewlett-Packard",
	      'name2'  => "Hewlett-Packard Co.",
	      'symbol' => "HPQ",
	      'url'    => "http://www.hp.com",
	      'price'  => 41.69,
	      'change' => -0.02,
	      'ratio'  => -0.05,
	      'price'  => 41.69,
	    ),
	    array(
	      'name'   => "IBM",
	      'name2'  => "International Business Machines Corp.",
	      'symbol' => "IBM",
	      'url'    => "http://www.ibm.com",
	      'price'  => 97.45,
	      'change' => -0.06,
	      'ratio'  => -0.06,
	      'price'  => 97.45,
	    ),
	    array(
	      'name'   => "Intel",
	      'name2'  => "Intel Corp.",
	      'symbol' => "INTC",
	      'url'    => "http://www.intel.com",
	      'price'  => 20.53,
	      'change' => -0.07,
	      'ratio'  => -0.34,
	      'price'  => 20.53,
	    ),
	    array(
	      'name'   => "Juniper Networks",
	      'name2'  => "Juniper Networks, Inc",
	      'symbol' => "JNPR",
	      'url'    => "http://www.juniper.net/",
	      'price'  => 18.96,
	      'change' => 0.5,
	      'ratio'  => 2.71,
	      'price'  => 18.96,
	    ),
	    array(
	      'name'   => "Microsoft",
	      'name2'  => "Microsoft Corp",
	      'symbol' => "MSFT",
	      'url'    => "http://www.microsoft.com",
	      'price'  => 30.6,
	      'change' => 0.15,
	      'ratio'  => 0.49,
	      'price'  => 30.6,
	    ),
	    array(
	      'name'   => "Oracle",
	      'name2'  => "Oracle Corp.",
	      'symbol' => "ORCL",
	      'url'    => "http://www.oracle.com",
	      'price'  => 17.15,
	      'change' => 0.17,
	      'ratio'  => 1.0,
	      'price'  => 17.15,
	    ),
	    array(
	      'name'   => "SAP",
	      'name2'  => "SAP AG",
	      'symbol' => "SAP",
	      'url'    => "http://www.sap.com",
	      'price'  => 46.2,
	      'change' => -0.16,
	      'ratio'  => -0.35,
	      'price'  => 46.2,
	    ),
	    array(
	      'name'   => "Seagate Technology",
	      'name2'  => "Seagate Technology",
	      'symbol' => "STX",
	      'url'    => "http://www.seagate.com/",
	      'price'  => 27.35,
	      'change' => -0.36,
	      'ratio'  => -1.3,
	      'price'  => 27.35,
	    ),
	    array(
	      'name'   => "Sun Microsystems",
	      'name2'  => "Sun Microsystems Inc.",
	      'symbol' => "SUNW",
	      'url'    => "http://www.sun.com",
	      'price'  => 6.33,
	      'change' => -0.01,
	      'ratio'  => -0.16,
	      'price'  => 6.33,
	    ),
	    array(
	      'name'   => "Yahoo",
	      'name2'  => "Yahoo! Inc.",
	      'symbol' => "YHOO",
	      'url'    => "http://www.yahoo.com",
	      'price'  => 28.04,
	      'change' => -0.17,
	      'ratio'  => -0.6,
	      'price'  => 28.04,
	    ),
	  ),
	],
];



$testTemplateSources = [];
$testTemplateSources['Post'] = $testTemplateSource1;
$testTemplateSources['Posts'] = $testTemplateSource2;
$testTemplateSources['TenjinBench'] = $testTemplateSource3;

?>
