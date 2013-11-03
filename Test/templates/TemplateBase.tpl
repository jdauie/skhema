{@TemplateBase}
<!DOCTYPE html>
<html>
<head>
	<title>{$title}</title>
	<link rel="stylesheet" href="theme/reset.css" type="text/css" />
	<link rel="stylesheet" href="theme/theme1.css" type="text/css" />
</head>
<body>
	
	<div id="header-wrapper">
		<div class="wrapper">
			<div id="header">
				{@Header}
					<h1><a href="{$root-url}">jacere.net</a></h1>
					{@Navigation}
						<div id="nav">
							<ul>
								{?nav-list}
									<li><a href="{$url}">{$text}</a></li>
								{/?}
							</ul>
							<ul id="global">
								{?nav-list-global}
									<li><a href="{$url}">{$text}</a></li>
								{/?}
							</ul>
						</div>
					{/@}
				{/@}
			</div>
		</div>
	</div>
	
	<div id="content-wrapper">
		<div class="wrapper">
			<div id="content-container">
				<div id="content">
					{$content}
				</div>
			</div>
			<div id="right">
				<div id="right-sub">
					{#Sidebar}
				</div>
			</div>
			<div id="left">
				<div id="left-sub"></div>
			</div>
		</div>
	</div>
	
	<div id="footer-wrapper">
		<div class="wrapper">
			<div id="footer"></div>
		</div>
	</div>
	
</body>
</html>
{/@}