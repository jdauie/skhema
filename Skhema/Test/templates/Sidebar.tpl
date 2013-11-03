{@Sidebar}
	{#SidebarSearch}
	{#SidebarSectionRecent}
	{#SidebarSectionArchives}
	{#SidebarSectionCategories}
{/@}

{@SidebarSearch}
	<div id="search">
		<form method="get" id="searchform">
			<div><input type="search" placeholder="Search" name="s" id="s" /></div>
		</form>
	</div>
{/@}

{@SidebarSection}
	<div class="right-sub-section" id="{$id}">
		<div class="title">
			<h2>{$title}</h2>
		</div>
		{$content}
	</div>
{/@}

{@SidebarSectionList}
	{^SidebarSection}
	{.content}
		<ul>
			{$list}
		</ul>
	{/.}
{/@}

{@SidebarSectionLinkList}
	{^SidebarSectionList}
	{.list}
		{?list}
			<li><a href="{$url}" title="{$text}">{$text}</a></li>
		{/?}
	{/.}
{/@}

{@SidebarSectionLinkListWithCount}
	{^SidebarSectionList}
	{.list}
		{?list}
			<li><a href="{$url}" title="{$text}">{$text}</a> <span>{$count}</span></li>
		{/?}
	{/.}
{/@}

{@SidebarSectionRecent}
	{^SidebarSectionLinkList}
	{.id}recent-posts{/}
	{.title}Recent Posts{/}
{/@}

{@SidebarSectionArchives}
	{^SidebarSectionLinkListWithCount}
	{.id}archives{/}
	{.title}Archives{/}
{/@}

{@SidebarSectionCategories}
	{^SidebarSectionLinkListWithCount}
	{.id}categories{/}
	{.title}Categories{/}
{/@}
