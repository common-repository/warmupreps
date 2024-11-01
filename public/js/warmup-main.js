var warmupApp = {
	"metric" : false,
	barWeight : function() {
		if (jQuery.cookie('bar_type') == 'bar_type_standard')
			return warmupApp.metric ? 10 : 20;
		else if (jQuery.cookie('bar_type') == 'bar_type_technique')
			return warmupApp.metric ?  7.5 : 15;
		else if (jQuery.cookie('bar_type') == 'bar_type_womens')
			return warmupApp.metric ? 15 : 35;
		else
			return warmupApp.metric ? 20 : 45;
	},
	stepSize : function() {
		return warmupApp.metric ? 2.5 : 5;
	},
	weights : function() {
		if (warmupApp.metric)
			return [20, 15, 10, 5, 2.5, 1.25];
		else
			return [45, 35, 25, 10, 5, 2.5];
	},
	"programs" : []
}
var mainWarmApp = {
	el : {
		'main_section' : false,
		'selected_program': false,
		'selected_excercise':false,
	},
	init:function(){
		console.log(jQuery(wmp_settings.selector).length);
	/*	jQuery(wmp_settings.selector).after(`<div class="warm-up-content">
	<div class="warmup-main-section">
			<div class="warmup-programs warmup-step">
			</div>
			<div class="warmup-excercises warmup-step">
			</div>
			<div class="warmup-workout warmup-step">
			</div>
	</div><div class="wmp_publish_to_blog hide">
				<div class="warmup-button">
				<a href="javascript:void(0)" class="button button-inline post_to_blog"> Post To Blog <i class="dashicons dashicons-update hide spin"></i></a>
				</div>
				<div class="warmup-response"></div>
			</div>`);*/
			this.getHtmlTemplate();
		this.initVars();
		this.initEvents();

	},
	initVars:function() {
		this.fetch_all_warmupdata();
		this.initializePreferences();
		this.el.main_section  =jQuery('.warmup-main-section');
		if (jQuery.cookie("units") == "units_kgs")
				warmupApp.metric = true;

	},
	initEvents:function() {
		this.render_warmup_first_step();
	},
	getHtmlTemplate:function() {
		if(jQuery(wmp_settings.selector).length == 0) return false;

			jQuery.ajax({
			url: wmp_vars.ajax_url+'?action=get_warmup_template_html',
			dataType: 'json',
			success: function(res) {
				if(res.status) {
					//warmupApp.programs = res.data;
					jQuery(wmp_settings.selector).after(`${res.htmldata}`);
				}
			},
			async: false
		});
	},
	initRangeSlider:function(selector) {
		var _self = this;
		if(selector.length !=0 ){
			var min = parseFloat(selector.attr('min'));
			var max = parseFloat(selector.attr('max'));
			var step = parseFloat(selector.attr('step'));
			//var max= 
			var slider = jQuery( "<div id='w-slider' class=' w-has-slider'></div>" ).insertAfter( selector ).slider({
      			min:min,
      			max: max,
      			step:step,
      			range: "min",
      			value: selector.val(),
      			slide: function( event, ui ) {
       				selector.val(ui.value) ;
       				_self.updateWorkout(ui.value);
      			}
    		});
	    	selector.on( "change", function() {
	      		slider.slider( "value", this.value );
	      		_self.updateWorkout(this.value);
	    	});
		}
	},
	updateWorkout:function(weight) {
		var workout_wrapper =jQuery('.w-workout-list');
		var excercise = warmupApp.programs[this.el.selected_program].exercises[this.el.selected_excercise];
		var	shortExerciseName = excercise.name.toLowerCase().replace(/ /g, '-');
		jQuery.cookie(shortExerciseName, weight, { expires: 30 });
		var wo_list = this.renderWorkoutList(excercise.workouts,weight);
		workout_wrapper.html(wo_list);

	},
	renderWorkoutList:function(workouts,currentWeight=100) {
		if(workouts.length ==0 ) return '';
		var workoutList ='';
		//console.log(currentWeight);
		for (k = 0; k < workouts.length; k++) {
			var workout = workouts[k];
			var setWeight = findWeight(workout, currentWeight);	
				//console.log(workout.sets, workout.reps, setWeight,workout);
			workoutList +=`<div class="w-wo-btn list-group-item">${printSet(workout.sets, workout.reps, setWeight)}
						(${calculateBarbellWeights(setWeight)})</div>`;
		}
		return workoutList;
	},
	fetch_all_warmupdata:function() {
	//	var data = {'post_id':wmp_settings.post_id};
		jQuery.ajax({
			url: wmp_vars.ajax_url+'?action=fetch_all_workouts',
			dataType: 'json',
			success: function(res) {
				if(res.status) {
					warmupApp.programs = res.data;
				}
			},
			async: false
		});
	},
	render_warmup_first_step:function() {
		if(warmupApp.programs.length == 0) return false;
		var stepHtml = `<div class="warm-up-intro">
		<div class="warm-up-heading"><h2>Getting Started</h2></div>
		<div class="warm-up-description">Choose a program, exercise, and then set your target weight.  Your warmup sets will then be automatically calculated.
		</div></div>`;

		for (i = 0; i < warmupApp.programs.length; i++) {
			var shortTitle = warmupApp.programs[i].title.toLowerCase().replace(/ /g, '-');
			stepHtml+= `<div class="w-program"> 
				<a class="w-btn" data-type="program" href="javascript:void(0)" data-index="${i}" data-slug="${shortTitle}">${warmupApp.programs[i].title}</a> 
			</div>`;

		}
		this.el.main_section.find('.warmup-programs').addClass('active').html(stepHtml);
	},
	render_warmup_second_step:function($p_index=''){
		var stepHtml ='';
		if(warmupApp.programs[$p_index].exercises.length != 0){
			var exercises = warmupApp.programs[$p_index].exercises;
			stepHtml+=`<div class="w-p-info"><div class="w-heading">${warmupApp.programs[$p_index].title}</div>
				<div class="w-desc">Choose an exercise from the program.</div>
				</div> <div class="w-ex-list">`;
			for (j = 0; j < exercises.length; j++) {
				shortExerciseName = exercises[j].name.toLowerCase().replace(/ /g, '-');
				stepHtml +=`<div class="w-excercises">
				<a class="w-btn" href="javascript:void(0)" data-type="exercise" data-index="${j}" 
				data-slug="${shortExerciseName}">${exercises[j].name}</a></div>`;
			}
			stepHtml+='</div>';
			this.el.main_section.data('program',$p_index);
			this.el.selected_program = $p_index;
		}
		stepHtml+="<div class='go-to-previous'><i class='dashicons-arrow-left-alt dashicons'> </i><a href='javascript:void(0)' class='prev_btn'> Choose to programs</a> </div>";
		this.el.main_section.find('.warmup-step').removeClass('active');
		this.el.main_section.find('.warmup-excercises').addClass('active').html(stepHtml);

		
	},
	render_warmup_third_step:function(e_index='') {
		var p_id = this.el.selected_program;
		var stepHtml = '';
		if(warmupApp.programs[p_id].exercises[e_index]) {
			var excercise = warmupApp.programs[p_id].exercises[e_index];
			var	shortExerciseName = excercise.name.toLowerCase().replace(/ /g, '-');

			if (jQuery.cookie(shortExerciseName) != undefined)
				var currentWeight = jQuery.cookie(shortExerciseName);
			else
				var currentWeight = "100";
			stepHtml+=`<div class="w-p-info"><div class="w-heading">${excercise.name}</div>
				<div class="w-desc">Choose your working weight. Your warmup sets will then be calculated 
				as well as the weights you need to put on each bar side.</div>
				</div> 
				<div id="workout-weight-container"> <div class="wo-c-item"><label for="workout-weight">Weight:</label> 
				   	<input type="number" name="w-weight" id="w-weight" 
				   	value="${currentWeight}" min="45" max="${excercise.max}" step="5" /></div> 	\
				</div>
				<div class="w-workout-list list-group">`;
			stepHtml +=this.renderWorkoutList(excercise.workouts,currentWeight);
			stepHtml +='</div>';
			this.el.main_section.data('excercise',e_index);
			this.el.selected_excercise = e_index;
		}

		//console.log(warmupApp.programs[p_id].exercises[e_index]);
		//console.log(e_index);
		stepHtml+="<div class='go-to-previous'> <i class='dashicons-arrow-left-alt dashicons'> </i><a href='javascript:void(0)' class='prev_btn'> Choose another excercise</a> </div>";
		this.el.main_section.find('.warmup-step').removeClass('active');
		this.el.main_section.find('.warmup-workout').addClass('active').html(stepHtml);

		this.initRangeSlider(jQuery('#w-weight'));
		jQuery('.wmp_publish_to_blog').removeClass('hide');
	},
	initializePreferences:function() {

			if (jQuery.cookie('bar_type') != undefined) {
				jQuery('input[name="bar-type"]').prop("checked", false);
				jQuery('input[id="' + jQuery.cookie('bar_type') + '"]').prop("checked", true);
				jQuery('input').attr('min', warmupApp.barWeight().toString());
			}
			else {
				var bar_type = 'bar_type_'+wmp_settings.bar_type;
				jQuery('input[id="'+bar_type+'"]').prop("checked", true)
			}
			if (jQuery.cookie('units') != undefined) {
				jQuery('input[name="units"]').prop("checked", false);
				jQuery('input[id="' + jQuery.cookie('units') + '"]').prop("checked", true);	
				jQuery('input').attr('step', warmupApp.stepSize().toString());
			}
			else {
				var units = 'units_'+wmp_settings.unit_system;
				jQuery('input[id="'+units+'"]').prop("checked", true);
				jQuery.cookie('units',units, { expires: 365 });

			}
	}
};
jQuery(function($){
	mainWarmApp.init();
	
	$(document).on('click','.w-btn',function(){
		var type = $(this).data('type');
		var index =$(this).data('index');
		if(type=='program') {
			mainWarmApp.render_warmup_second_step(index);
		}
		else if(type=='exercise') {
			mainWarmApp.render_warmup_third_step(index);

		}	
	})
	$(document).on('click','.go-to-previous',function(){
		var current_active_step =$('.warmup-step.active').index();
		if(current_active_step > 0){
			$('.warmup-step').removeClass('active').eq(current_active_step-1).addClass('active');
			$('.wmp_publish_to_blog').addClass('hide');
		}
	});
	$("input[name='units']").change(function() {
		$.cookie('units', $(this).attr('id'), { expires: 365 });
		warmupApp.metric = !warmupApp.metric;
		$('input').attr('min', warmupApp.barWeight().toString());
		$(":input[min]").each(function() {
			var currentMax = $(this).attr('max');
			if (warmupApp.metric)
				$(this).attr('max', roundDown(Math.floor(currentMax * 0.45359)));
			else
				$(this).attr('max', roundDown(Math.floor(currentMax * 2.20462)));
		});		
		$('#w-weight').trigger('change');
	});

	$("input[name='bar-type']").change(function() {
		$.cookie('bar_type', $(this).attr('id'), { expires: 365 });
		$('input').attr('min', warmupApp.barWeight().toString());
		$('#w-weight').trigger('change');
	});
	$(document).on('click','.post_to_blog',function(){
		var selector =$(this);
		var p_id = mainWarmApp.el.selected_program;
		var e_id = mainWarmApp.el.selected_excercise;
		var is_workout_step_active = $('.warmup-workout').hasClass('active');

			var e_name =  warmupApp.programs[p_id].exercises[e_id].name;
			var post_title = `${warmupApp.programs[p_id].title} - ${e_name}`;
			var content = $('.w-workout-list')[0].outerHTML;
			var data = {'action':'sava_data_to_blog','content':content,'title':post_title};
				data.weight = jQuery('#w-weight').val();
				data.bar_type =  jQuery.cookie('bar_type');
			$.ajax({
				url:wmp_vars.ajax_url,
				data:data,
				type:'POST',
				beforeSend:function(){
					selector.find('i').removeClass('hide');
					$('.warm-up-content').css({'opacity':0.5});
				},
				success:function(res){
					if(res.msg) {
						$('.warmup-response').html(res.msg);
					}
					selector.find('i').addClass('hide');
					$('.warm-up-content').css({'opacity':1});
					//window.location.reload(true);
				}
			})
	})
});

function initializePreferences() {


}


function calculateBarbellWeights(weight) {
	weight -= warmupApp.barWeight();

	if (weight === 0)
		return "Bar";

	weight /= 2;

	var weight_45 = 0;
	var weight_35 = 0;
	var weight_25 = 0;
	var weight_10 = 0;
	var weight_5 = 0;
	var weight_2half = 0;

	while (weight > 0) {
		if (weight >= warmupApp.weights()[0]) {
			weight_45 += 1;
			weight -= warmupApp.weights()[0];
			continue;
		}
		else if (weight >= warmupApp.weights()[1]) {
			weight_35 += 1;
			weight -= warmupApp.weights()[1];
			continue;					
		}
		else if (weight >= warmupApp.weights()[2]) {
			weight_25 += 1;
			weight -= warmupApp.weights()[2];
			continue;					
		}
		else if (weight >= warmupApp.weights()[3]) {
			weight_10 += 1;
			weight -= warmupApp.weights()[3];
			continue;					
		}
		else if (weight >= warmupApp.weights()[4]) {
			weight_5 += 1;
			weight -= warmupApp.weights()[4];
			continue;					
		}
		else {
			weight_2half += 1;
			weight -= warmupApp.weights()[5];
			continue;					
		}				
	}

	var result = "";

	if (weight_45 != 0) 
		result += printWeightCountSubstring(weight_45, warmupApp.weights()[0].toString());
	if (weight_35 != 0)
		result += printWeightCountSubstring(weight_35, warmupApp.weights()[1].toString());
	if (weight_25 != 0)
		result += printWeightCountSubstring(weight_25, warmupApp.weights()[2].toString());
	if (weight_10 != 0)
		result += printWeightCountSubstring(weight_10, warmupApp.weights()[3].toString());
	if (weight_5 != 0) 
		result += printWeightCountSubstring(weight_5, warmupApp.weights()[4].toString());
	if (weight_2half != 0)
		result += printWeightCountSubstring(weight_2half, warmupApp.weights()[5].toString());

	return jQuery.trim(result);
}

function printWeightCountSubstring(weight, text) {
	if (weight > 1)
		return weight + "x" + text + " ";
	else
		return text + " ";
}

function findWeight(exercise, weight) {
	if (exercise.multiplier != undefined)
		return roundDown(exercise.multiplier * weight);
	else
		return exercise.weight;
}

function printSet(sets, reps, weight) {
	var output = sets + "x" + reps + " " + weight;
	if (warmupApp.metric)
		return output + " kgs"
	else 
		return output + " lbs"
}

function roundDown(num) {
	if (num - (num % warmupApp.stepSize()) < warmupApp.barWeight())
		return warmupApp.barWeight();
	else
		return num - (num % warmupApp.stepSize());
}
