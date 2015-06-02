<?php
	if (!defined('SMF'))
		die('No direct access...');
		
	//Arrays to store user-registered events, functions and actions.
	$filter_events = array();
	 
	 //Load Plugins
	foreach(glob('Plugins/*_init.php')  as $plugin) {
		require_once($plugin);
	} 
	
	//Functions for Filter/Action Hooks
	function call_hook($event, $content = array(), $filter = true) {
	 
		global $modSettings, $filter_events;
		
		$content_res = array();
		
		if(isset($filter_events[$event]))
		{
			foreach($filter_events[$event] as $idp => $func) {
				foreach($func as $call) {
					if(!empty($modSettings[$idp])){
						if(file_exists($call) && $event == 'hook_load_file'){
						
							require_once($call);
							
						}elseif(function_exists($call) && $filter == true){
						
							$content_res[$idp] = call_user_func_array($call, $content);	
								
						}elseif(function_exists($call) && $filter == false){
						
							call_user_func_array($call, $content);
								
						}
					}
				}
			}
		}
		return $content_res;
	}
	 
	function register_hook($event, $func, $id_plug, $is_smf_hook = false, $priorty = 10)
	{
		global $modSettings, $smcFunc, $filter_events;
		
		if($is_smf_hook == false){
			$filter_events[$event][$id_plug][] = $func;
			
		}else{
			
			$request = $smcFunc['db_query']('', '
				SELECT value
				FROM {db_prefix}settings
				WHERE variable = {string:variable}',
				array(
					'variable' => $event,
				)
			);
			list($current_functions) = $smcFunc['db_fetch_row']($request);
			$smcFunc['db_free_result']($request);

			if (!empty($current_functions))
			{
				$current_functions = explode(',', $current_functions);
				if (in_array($func, $current_functions))
					return;

				$permanent_functions = array_merge($current_functions, array($func));
			}
			else
				$permanent_functions = array($func);
				
			$smcFunc['db_insert']('replace', '{db_prefix}settings',
				array(
					'variable' => 'string',
					'value' => 'string',
				),
				array(
					array ($event ,implode(',', $permanent_functions)),
				),
				array()
			);

			$functions = empty($modSettings[$event]) ? array() : explode(',', $modSettings[$event]);

			if (in_array($func, $functions))
				return;

			$functions[] = $func;
			
			$modSettings[$event] = implode(',', $functions);
		}
	}
?>