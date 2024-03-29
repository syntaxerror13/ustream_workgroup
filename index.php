<?php
error_reporting(-1);
include('slack.php');
include('Project.php');
include('User.php');
include('Event.php');

require_once('config.php');

if ($prod) {
	require_once('DB.php');
	DB::init($dbconfig);
	Slack::init($workgroups_webhook_url);
	Page::$mocked = false;
}
else
{
	Page::$mocked = true;
}

class Page
{
	public static $title;
	public static $mocked;

	public static function getCommand()
	{
		$q = isset($_GET['q']) ? $_GET['q'] : 'project';
		$a = explode('/', $q);
		return array('command' => $a[0], 'params' => array_slice($a, 1));
	}

	public static function renderProject($name)
	{
		if (Page::$mocked)
			$p = new Project('Slack workgroup tool', 'A tool for mapping project members and focus', 'syntaxerror'); 
		else
			$p = Project::load($name);
		if ($p == null) return '';

		Page::$title = 'Project '.$p->name;

		$output = '<h2>Project summary</h2><div class="project"><p>'.$p->desc.'</p></div>';
		
		if (Page::$mocked)
			$members = array(
				array('name' => 'syntaxerror', 'focus' => false),
				array('name' => 'csabi', 'focus' => true),
				array('name' => 'someone', 'focus' => false),
				);
		else
		{
			$members = $p->getMembers();
			$p -> loadStats();
		}
		
		if (Page::$mocked)
			$logs = array(new Event('2015-01-01', 'Whatever', 'syntaxerror', 'joined the project.'),
				new Event('2015-01-01', 'Whatever', 'csabi', 'left the project.'),	
				);
		else
			$logs = $p->getLog(20);

		$output .= '<h2>Members</h2>';		
		$output .= '<p><span class="small">Total: '.$p->members.', Focusing: '.$p->focusingMembers.'</span></p>';
		foreach ($members as $m)
		{
			$owner = $m['name'] == $p->owner ? ' owner' : '';
			$focus = $m['focus'] ? ' focus' : '';
			$output .= '<div class="member'.$focus.$owner.'"><a href="index.php?q=user/'.$m['name'].'">'.$m['name'].'</a></div>';
		}
		if (!count($members)) 
		{
			$output .= '<div class="member owner"><a href="index.php?q=user/'.$p->owner.'">'.$p->owner.'</a></div>';
			$output .= '<p>This project has no additional members yet.</p>';
		}

		$routput = '<h2>Recent activity</h2>';
		foreach ($logs as $l)
		{
			$smallActions = array("join", "leave", "focus", "unfocus", "slackroom", "ratio", "owner"); 
			if (array_search($l->action, $smallActions) === false) 
				$routput .= '<div class="log">'.$l->time.' '.$l->formatMessage().'</div>';
			else
				$routput .= '<div class="log"><span class="small">'.$l->time.' '.$l->formatMessage().'</span></div>';
		}
		if (!count($logs)) 
		{
			$routput .= '<p>Nothing has happened here yet.</p>';
		}

		$output .= '<div class="footer"><p><a href="index.php?q=project">More projects...</a></p></div>';
		return array($output, $routput);
	}


	public static function renderProjects()
	{
		if (Page::$mocked)
			$projects = array(new Project('Slack workgroup tool', 'A tool for mapping project members and focus', 'syntaxerror'),
				new Project('PjM job description', 'Have a document about their roles', 'csabi'),
			);
		else
			$projects = Project::listAll();
		
		Page::$title = 'Workgroup projects';

		$output = '';
		foreach ($projects as $p)
		{
			$output .= '<div class="project"><a href="index.php?q=project/'.$p->name.'">'.$p->name.'</a><br/><span class="small">by @'.
				$p->owner.' Focus/Members: '.$p->focusingMembers.'/'.$p->members.'</span>'.
				'<p>'.$p->desc.'</p></div>';
		}
		return array($output, '');

	}

	public static function renderUser($name)
	{
		if (Page::$mocked)
			$u = new User('csabi');	
		else
			$u = User::load($name);

		if (!$u) return '';

		Page::$title = 'User '.$u->name;

		$output = '';
		$output .= '<h2>Projects</h2>';

		if (Page::$mocked)
			$projects = array(
				array("name" => "Consul", "focus" => true),
				array("name" => "Logging", "focus" => false)
				);
		else
			$projects = $u->getProjects();

		foreach ($projects as $p)
		{
			$output .= '<div class="project"><a href="index.php?q=project/'.$p['name'].'">'.$p['name'].'</a> '.($p['focus'] ? '[x]' : '').
			'<br /><span class="small">'.$p['fmembers'].' of '.$p['members'].' member(s) focusing</span></div>';
		}
		$output .= '<div class="footer"><a href="index.php?q=user">Browse more users...</a></div>';
		return array($output, '');
	}

	public static function renderUsers()
	{
		if (Page::$mocked)
			$users = array(new User('csabi'), new User('syntaxerror'));
		else
			$users = User::listAll();

		Page::$title = 'Workgroup users';

		$output = '';
		foreach ($users as $u)
		{
			$output .= '<div class="member"><a href="index.php?q=user/'.$u->name.'">'.$u->name.'</a></div>';

		}
		return array($output, '');
	}
}

/*
	project - list of all projects
	project/@id - describe a project - list all members, display events
	user - list all users
	user/@id - describe a project
*/

Page::$title = 'Ustream Workgroups';
$command = Page::getCommand();

switch ($command['command']) 
{
	case 'project':
		if (isset($command['params'][0]))
			$output = Page::renderProject($command['params'][0]);
		else
			$output = Page::renderProjects();
		break;
	case 'user':
		if (isset($command['params'][0]))
			$output = Page::renderUser($command['params'][0]);
		else
			$output = Page::renderUsers();
		break;
	
	default:
		break;
}





?><html>
<head>
	<title><?php echo Page::$title; ?></title>
	<link rel="stylesheet" href="style.css" />
	<meta name="viewport" content="width=device-width; initial-scale=1.0" />
</head>
<body>
<div class="main">
	<div class="header"><h1><?php echo Page::$title; ?></h1></div>
	<div class="content">
		<div class="padded"><?php echo $output[0]; ?></div>
	</div>	
	<div class="content">
		<div class="padded"><?php echo $output[1]; ?></div>
	</div>	
</div>
</body>
</html>