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

		$output = '<div class="project"><h1>'.$p->name.'</h1><span class="small">by @'.
			$p->owner.'</span><p>'.$p->desc.'</p></div>';
		
		if (Page::$mocked)
			$members = array(
				array('name' => 'syntaxerror', 'focus' => true),
				array('name' => 'csabi', 'focus' => false),
				);
		else
			$members = $p->getMembers();
		
		if (Page::$mocked)
			$logs = array(new Event('2015-01-01', 'Whatever', 'syntaxerror', 'joined the project.'),
				new Event('2015-01-01', 'Whatever', 'csabi', 'left the project.'),	
				);
		else
			$logs = $p->getLog();

		$output .= '<h2>Members</h2>';
		foreach ($members as $m)
		{
			$output .= '<div class="member"><a href="index.php?q=user/'.$m['name'].'">'.$m['name'].'</a> '.($m['focus'] ? '[x]' : '').'</div>';
		}
		$output .= '<h2>Log</h2>';
		foreach ($logs as $l)
		{
			$output .= '<div class="log"><span class="small">'.$l->time.' <a href="index.php?q=user/'.$l->user.'">'.$l->user.'</a></span><br />'.$l->message.'</div>';
		}
		return $output;
	}


	public static function renderProjects()
	{
		if (Page::$mocked)
			$projects = array(new Project('Slack workgroup tool', 'A tool for mapping project members and focus', 'syntaxerror'),
				new Project('PjM job description', 'Have a document about their roles', 'csabi'),
			);
		else
			$projects = Project::listAll();
		
		$output = '<h1>Projects</h1>';
		foreach ($projects as $p)
		{
			$output .= '<div class="project"><a href="index.php?q=project/'.$p->name.'">'.$p->name.'</a><br/><span class="small">by @'.
				$p->owner.'</span><p>'.$p->desc.'</p></div>';
		}
		return $output;

	}

	public static function renderUser($name)
	{
		if (Page::$mocked)
			$u = new User('csabi');	
		else
			$u = User::load($name);

		if (!$u) return '';
		$output = '<div class="user"><h1>'.$u->name.'</h1></div>';
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
			$output .= '<div class="project"><a href="index.php?q=project/'.$p['name'].'">'.$p['name'].'</a> '.($p['focus'] ? '[x]' : '').'</div>';
		}
		return $output;
	}

	public static function renderUsers()
	{
		if (Page::$mocked)
			$users = array(new User('csabi'), new User('syntaxerror'));
		else
			User::listAll();

		$output = '<h1>Users</h1>';
		foreach ($users as $u)
		{
			$output .= '<div class="user"><a href="/index.php?q=user/'.$u->name.'">'.$u->name.'</a></div>';
		}
		return $output;
	}
}

/*
	project - list of all projects
	project/@id - describe a project - list all members, display events
	user - list all users
	user/@id - describe a project
*/

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
	<title>Ustream workgroups</title>
	<link rel="stylesheet" href="style.css" />
</head>
<body>
<?php echo $output; ?>	
</body>
</html>