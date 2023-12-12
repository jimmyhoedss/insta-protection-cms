var _IP = _IP || {};
var _Utils = _Utils || {};
var menu_index = 0;
_IP.Data = {};
_IP.Variables = {
	//toDO use production url
	API_URL: 'http://api.instaprotection.localhost/v1/'
};


_IP.Dashboard = (function(){
	function init(){
		var ctx = document.getElementById('revenue-chart');
		var ctx_plan_reg = document.getElementById('plan-registration');
		var ctx_claim = document.getElementById('claims');
		var colorArray = ["#FF6A00ff", "#FF6F08ff", "#FF7310ff", "#FF7818ff", "#FF7D20ff", "#FF8128ff", "#FF8630ff", "#FF8B38ff", "#FF8F40ff", "#FF9448ff", "#FF9950ff", "#FF9D58ff", "#FFA260ff", "#FFA768ff", "#FFAB70ff", "#FFB079ff", "#FFB481ff", "#FFB989ff", "#FFBE91ff", "#FFC299ff", "#FFC7A1ff", "#FFCCA9ff", "#FFD0B1ff", "#FFD5B9ff", "#FFDAC1ff", "#FFDEC9ff", "#FFE3D1ff", "#FFE8D9ff", "#FFECE1ff", "#FFF1E9ff"];
		var planRegistrationArr = [], grossMarginArr = [], claimArr = [];
		var planCategoryColor = {
			// 'LSLT': "#FFF1E9ff",
			// 'LSSE': "#FFD0B1ff",
			// 'LSSP': "#FFAB70ff",
			// 'LSTB': "#FF8630ff"
			'LSSP': "#FFD5B9ff",
			'LSSE': "#FFA260ff",
			'LSLT': "#FF8630ff",
			'LSTB': "#FF6A00ff"
		}

//trigger
        $('#plans').on('change', function (e) {
        	var plan_id = $('#plans').val(); 
			displayPlanInfo(plan_id);
			displayClaimInfo(plan_id);
			displayRevenueInfo(plan_id);

	    });

	    $('#claim-plans').on('change', function (e) {
        	var plan_id = $('#claim-plans').val(); 
			displayClaimInfo(plan_id);
	    });

	    $('#revenue-plans').on('change', function (e) {
        	var plan_id = $('#revenue-plans').val(); 
			displayRevenueInfo(plan_id);
	    });

	    $('#plans').trigger('change');
	    $('#claim-plans').trigger('change');
	    $('#revenue-plans').trigger('change');
//trigger

		function formChartArray(arr) {
			var tempArr = [];
			for (var i = arr.length - 1; i >= 0; i--) {
				// console.log('plans  ------>',arr[i]);
				// var color = random_rgba(arr[i].plan_id);
				var graphConf = {
			        label: arr[i].name,
			        data: arr[i].value,
			        backgroundColor: [
			        	colorArray[i]
			            // color
			        ],
			        borderColor: [
			        	colorArray[i]
			        ],
			        borderWidth: 2,
			        fill: false,
			        // hidden: true
			    };
			    // console.log("gross",graphConf)
			    tempArr.push(graphConf);
			}

			return tempArr;
		}

		//ajax call
		function displayPlanInfo(plan_id) {
	        $.ajax({
	            type: "POST",
	            url: "plan-info",
	            data: {plan_id: plan_id},
	            success: function (res) {
	                // alert(res);
	                d = JSON.parse(res)
	                data = d[0];
	                // console.log(data.number_of_register)
	                $('#total_reg_plan').html(data.number_of_register);
	                $('#approved').html(data.number_of_approved);
	                $('#cancelled').html(data.number_of_cancelled);
	                $('#pending_approvval').html(data.number_of_pending_approval);
	                $('#clarification').html(data.number_of_seeking_clarification);
	            },
	            error: function (exception) {
	            	// console.log("fail")
	                console.log("Connection error");
	            }
	        });
	    }

	    function displayClaimInfo(plan_id) {
	        $.ajax({
	            type: "POST",
	            url: "claim-info",
	            data: {plan_id: plan_id},
	            success: function (res) {
	                // alert(res)
	                // console.log("claim", res);
	                d = JSON.parse(res)
	                data = d[0];
	                // console.log(data.number_of_register)
	                $('#total_reg_claim').html(data.number_of_register);
	                $('#claim_approved').html(data.number_of_approved);
	                $('#claim_pending_approvval').html(data.number_of_pending_approval);
	                $('#claim_clarification').html(data.number_of_seeking_clarification);
	                $('#claim_ratio').html(data.claim_ratio);
	            },
	            error: function (exception) {
	            	// console.log(exception)
	                console.log("Connection error");
	                // alert(exception);
	            }
	        });
	    }

	    function displayRevenueInfo(plan_id) {
	        $.ajax({
	            type: "POST",
	            url: "revenue-info",
	            data: {plan_id: plan_id},
	            success: function (res) {
	                d = JSON.parse(res)
	                data = d[0];
	                $('#revenue').html(data.total_revenue);
	                $('#total_premium').html(data.total_premium);
	                $('#total_dealer').html(data.total_dealer);
	                $('#total_retail').html(data.total_retail);
	            },
	            error: function (exception) {
	            	console.log(exception)
	                console.log("Connection error");
	                // alert(exception);
	            }
	        });
	    }

		// console.log("time series arr : ", claimData) //pass from controller

    	var subChart2 = new Chart(document.getElementById("sub-claims"), {
		    type: 'bar',
		    data: {
		      labels: [],
		      datasets: [
		        {
		          label: "Number of Plans",
		          backgroundColor: ["#3e95cd", "#8e5ea2","#3cba9f","#e8c3b9","#c45850"],
		          data: []
		        }
		      ]
		    },
		    options: {
		      
				animation: {
				    duration: 0
				},
				maintainAspectRatio: false,
				responsive: true,
				legend: { display: false },
				title: {
					display: true,
					text: 'No of Claim By Category'
				},
		      scales: {
			      	yAxes: [{
			      		stacked: true,
			      		afterDataLimits(scale) {
			              	// add 5% to both ends of range
			              	var range = scale.max-scale.min;
			                var grace = range * 0.15; 
			              	scale.max += grace ;
			         			// scale.min -= grace;
			              },
			            ticks: {
			                beginAtZero: true,
			                stepValue: 5,
			                stepSize: 1
			            }
			        }],
			        xAxes: [{
			        	stacked: true,
			            ticks: {
			                // display: false,
			                fontSize: 11
			            }
			        }]
			    },
		    }
		});

		var subChart = new Chart(document.getElementById("sub-plan-registration"), {
		    type: 'bar',
			data: {
				labels: [],
				datasets: []
		    },
		    options: {
		    	animation: {
			        duration: 0
			    },
		    	maintainAspectRatio: false,
				responsive: true,
		      legend: { display: false },
		      title: {
		        display: true,
		        text: 'Number of Policy By Category'
		      },
		      scales: {
			      	yAxes: [{
			      		stacked: true,
			      		// grace: "5%",
		      		 	afterDataLimits(scale) {
			              	// add 5% to both ends of range
			              	var range = scale.max-scale.min;
			                var grace = range * 0.15; 
			              	scale.max += grace ;
			         			// scale.min -= grace;
			              },
			            ticks: {
			                beginAtZero: true,
			                stepValue: 5,
				            // max: 30,
				            // grace: '50%'
			                stepSize: 1
			            }
			        }],
			        xAxes: [{
			        	stacked: true,
			            ticks: {
			                // display: false,
			                fontSize: 11
			            }
			        }]
			    },
		    }
		});

		planRegistration();
		claimRegistration();
		function claimRegistration() {
			var tempArr = [];
			var conf = {
			  label: 'Lifestyle',
			  borderColor: '#FF6F08ff',
			  data: claimData
			};
				var conf2 = {
			  label: 'Household',
			  borderColor: '#FF8F40ff',
			  data: []
			};
			
				var conf3 = {
			  label: 'Automotive',
			  borderColor: '#FF9D58ff',
			  data: []
			};
			
				var conf4 = {
			  label: 'Personal',
			  borderColor: '#FFC299ff',
			  data: []
			};

			tempArr.push(conf);
			tempArr.push(conf2);
			tempArr.push(conf3);
			tempArr.push(conf4);
        	plotGraphTimeSeries2(ctx_claim, tempArr, 'Claim Registration', subChart2);
	        
    	}


    	function planRegistration() {
			var tempArr = [];
			var dataConf = {
			  label: 'Lifestyle',
			  borderColor: '#FF6F08ff',
			  data: planData
			};
			
			var dataConf2 = {
			  label: 'Household',
			  borderColor: '#FF8F40ff',
			  data: []
			};
			
				var dataConf3 = {
			  label: 'Automotive',
			  borderColor: '#FF9D58ff',
			  data: []
			};
			
				var dataConf4 = {
			  label: 'Personal',
			  borderColor: '#FFC299ff',
			  data: []
			};

			tempArr.push(dataConf);
			tempArr.push(dataConf2);
			tempArr.push(dataConf3);
			tempArr.push(dataConf4);
           //plot graph
        	plotGraphTimeSeries2(ctx_plan_reg, tempArr, 'Policy Registration', subChart);
	        
    	}
    	//plans and claims
	    function plotGraphTimeSeries2(ctx, dataSets, chart_title, subChart, chart_type = 'line') {
	    	 var myChart = new Chart(ctx, {
				  type: chart_type,
				  data: {
				    datasets: dataSets
				  },
				  options: {
				  	// spanGaps: true,
				  	// showLine: false,
				  	maintainAspectRatio: false,
					responsive: true,
				  	elements: {
				        line: {
				            tension: 0
				        }
				    },
				    // tooltips: {
				    //   mode: 'index',
				    //   intersect: true,
				    // },
				    // events: ['click', 'touchstart', 'hover'], //for tooltips
				    onClick: function(evt) {   
				    	var labelArr = [];
				    	var chartData = [];
				      	var element = myChart.getElementAtEvent(evt);
				      	var index = element[0]._index; //get index of point in charjs
      					var selectedData = dataSets[0].data[index]; //get data from selected point
      					var tooltips = selectedData.dataForSubGraph;
				      	// console.log("from onlick event :", tooltips.map(o => o.LSSP));
				      	// console.log("index :", index);
				      	var labels = tooltips.map(o => o.label);
				      	var fields = tooltips[0];
				      	// console.log("fields", fields);
				      	for (var prop in fields) {
				      		if(prop != 'label') {
				      			var data = {
					      			label: prop,
					      			backgroundColor: planCategoryColor[prop],
					      			data: tooltips.map(function(element, index, array){
										// console.log(this) // 80ß
										var props = this;
										let v = props[0];
										// console.log("element", element[v])
										return element[v];
										// return element.prop
									}, [prop])
					      		}
				      			chartData.push(data);
				      		}
				      	}
				      	subChart.data.labels = labels;
				      	subChart.data.datasets = chartData;
						subChart.update();

				    },
				    tooltips: {
				    //   callbacks: {
				    //     label: function(tooltipItem, data) {
				    //       var item = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]; //get data from data set
				    //       	//loop the tooltips for display or transfer to another view
				    //       	var labelArr = [];
				    //       	var chartData = [];
				    //       	console.log("date: ", item.x);
				    //       	for (var i = 0; i < item.tooltips.length; i++) {
				    //       		console.log("plan name = ",item.tooltips[i].plan_name);
				    //       		labelArr.push(item.tooltips[i].plan_name)
								// chartData.push(item.tooltips[i].value)
				    //       	}
								// subChart.data.labels = labelArr;
								// subChart.data.datasets[0].data = chartData;
								// subChart.update();

				    //     },
				    //   }
				    },
				    hover: {
				      mode: 'index',
				      intersect: true
				    },
				    scales: {
				        xAxes: [{
					        type: 'time',
					        time: {
			                    unit: 'day'
			                },
			                // ticks: {
			                // 	min: "2020-10-08",
			                //     max: "2019-11-30",
			                // }
				      	}],
				      	// xAxes: [{
			        //         type: 'time',
			        //         position: 'bottom',
			        //         time: {
			        //             min: "2019-1-1",
			        //             max: "2019-2-28",
			        //             unit: "month",
			        //             displayFormats: {
			        //                 "month": "dd-MM-YYYY",
			        //             }
			        //         }
			        //     }]
				      	yAxes: [{
				            ticks: {
				                beginAtZero: true,
				                // stepSize: 10
				            }
				        }]
				    },
				    layout: {
			            padding: {
			                left: 20,
			                right: 30,
			                top: 10,
			                bottom: 10
			            }
			        },
			        legend: {
			            display: true,
			            position: 'bottom',
			            labels: {
			                fontColor: 'rgb(0, 0, 0)',
			                fontSize: 10,
			            	padding: 10,
			            },
			            title: {
				          display: true,
				          fontSize: 10,
				          text: 'Product',
				        }
			        },
			        title: {
			            display: true,
		                fontSize: 20,
			            text: chart_title
			        },
			        animation: {
			            onProgress: function(animation) {
			                // progress.value = animation.currentStep / animation.numSteps;
			                $(".chart-container .loading").css("display", "block");
			                // console.log("animation", animation);
			            },
			            onComplete: function(animation) {
			            	$('.chart-container .loading').css("display", "none");
			                // progress.value = animation.currentStep / animation.numSteps;
			            },
			        }
				  }
				});
	    }

	    plotGraphTimeSeries(ctx, 'Gross Sales');
	    //gross revenue
	    function plotGraphTimeSeries(ctx, chart_title, chart_type = 'line') {
	    	 var myChart = new Chart(ctx, {
				  type: chart_type,
				  data: {
				    datasets: [
						{
						  label: 'Sales In Last Year',
						  borderColor: '#FF6F08ff',
						  data: grossSales
						}
				    ]
				  },
				  options: {
				  	// spanGaps: true,
				  	// showLine: false,
				  	maintainAspectRatio: false,
					responsive: true,
				  	elements: {
				        line: {
				            tension: 0
				        }
				    },
				    hover: {
				      mode: 'index',
				      intersect: true
				    },
				    scales: {
				        xAxes: [{
					        type: 'time',
					        time: {
			                    unit: 'month'
			                },
			                // ticks: {
			                // 	min: "2020-10-08",
			                //     max: "2019-11-30",
			                // }
				      	}],
				      	// xAxes: [{
			        //         type: 'time',
			        //         position: 'bottom',
			        //         time: {
			        //             min: "2019-1-1",
			        //             max: "2019-2-28",
			        //             unit: "month",
			        //             displayFormats: {
			        //                 "month": "dd-MM-YYYY",
			        //             }
			        //         }
			        //     }]
				      	yAxes: [{
				            ticks: {
				                // beginAtZero: true,
				                // stepSize: 10
				            }
				        }]
				    },
				    layout: {
			            padding: {
			                left: 20,
			                right: 30,
			                top: 10,
			                bottom: 10
			            }
			        },
			        legend: {
			            display: true,
			            position: 'bottom',
			            labels: {
			                fontColor: 'rgb(0, 0, 0)',
			                fontSize: 10,
			            	padding: 10,
			            },
			            title: {
				          display: true,
				          fontSize: 10,
				          text: 'Product',
				        }
			        },
			        title: {
			            display: true,
		                fontSize: 20,
			            text: chart_title
			        },
			        animation: {
			            onProgress: function(animation) {
			                // progress.value = animation.currentStep / animation.numSteps;
			                $(".chart-container .loading").css("display", "block");
			                // console.log("animation", animation);
			            },
			            onComplete: function(animation) {
			            	$('.chart-container .loading').css("display", "none");
			                // progress.value = animation.currentStep / animation.numSteps;
			            },
			        }
				  }
				});
	    }

		

	}
	return {
		init: init
	}
})();

_IP.Kbn = (function(){
	function init(){
		console.log("kbn index ")
		var ctx = document.getElementById('plan-registration');
		var colorArr = ['red', 'green', 'orange', 'pink', 'brown', 'blue', 'yellow', 'aqua'];
		var s1 = {
		  label: 's1',
		  borderColor: 'blue',
		  data: [
		    { x: '2017-01-06 18:39:30', y: 100 },
		    { x: '2017-01-07 18:39:28', y: 101 },
		  ]
		};

		var s2 = {
		  label: 's2',
		  borderColor: 'red',
		  data: [
		    { x: '2017-01-07 18:00:00', y: 90 },
		    { x: '2017-01-08 18:00:00', y: 105 },
		  ]
		};

		// var myChart = new Chart(ctx, {
		//   type: 'line',
		//   data: {
		//     datasets: [
		// 	    	{
		// 	        label: 'Chart 1',
		// 	        data: [{x: '2017-01-06 18:39:30', y: 100 }, {x: '2017-02-07 18:39:28', y: 101 },{x: '2018-03-07 18:39:28', y: 201 },{x: '2017-10-07 18:39:28', y: 155 }],
		// 	        showLine: true,
		// 	        fill: false,
		// 	        borderColor: 'rgba(0, 200, 0, 1)'
		//     	},
		//     ]
		//   },
		//   options: {
		//     tooltips: {
		//       mode: 'index',
		//       intersect: false,
		//     },
		//     hover: {
		//       mode: 'nearest',
		//       intersect: true
		//     },
		//     scales: {
		//         xAxes: [{
		// 	        type: 'time'
		//       }]
		//     },
		//   }
		// });
testTimeSeriesGraph();


		function testTimeSeriesGraph() {
	        $.ajax({
	            type: "GET",
	            url: _IP.Variables.API_URL+"elasticsearch/els",
	            data: {plan_id: 1},
	            success: function (res) {
	            	var tempArr = [];
	            	// console.log(res.data.aggregations.plans.buckets);
	            	var data = res.data.aggregations.plans.buckets;
					for (var i = 0; i < data.length; i++) {
						var dataSets = [];
						let plan_name = data[i].key;
						// console.log("plan nanem ::", data[i].key);
						var dateArr = data[i].date.buckets;
						// console.log(dateArr);
						for (var j = 0; j < dateArr.length; j++) {
							// var date = dateArr[i].key_as_string //this is date
							// var count = dateArr[i].doc_count //this is number
							var tempData = { x: dateArr[j].key_as_string, y: dateArr[j].doc_count}; //time series data format
							dataSets.push(tempData);
						}
						var conf = {
						  label: plan_name,
						  borderColor: colorArr[i],
						  data: dataSets
						};

						tempArr.push(conf);

					}
	               // console.log("tempAtt data :",tempArr);
	               //plot graph
	               plotGraph(ctx, tempArr, 'Registration');

	            },
	            error: function (exception) {
	            	// console.log("fail")
	                console.log("Connection error");
	            }
	        });
	    }

	    function plotGraph(ctx, dataSets, chart_title, chart_type = 'line') {
	    	 var myChart = new Chart(ctx, {
				  type: chart_type,
				  data: {
				    datasets: dataSets
				  },
				  options: {
				  	maintainAspectRatio: false,
					responsive: true,
				  	elements: {
				        line: {
				            tension: 0
				        }
				    },
				    // tooltips: {
				    //   mode: 'index',
				    //   intersect: true,
				    // },
				    tooltips: {
				      callbacks: {
				        label: function(tooltipItem, data) {
				          var item = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
									return item.y  + ' ' + "im pig"
				        },
				        chart: 'line'
				      }
				    },
				    hover: {
				      mode: 'dataset',
				      intersect: true
				    },
				    scales: {
				        xAxes: [{
					        type: 'time',
					        time: {
			                    unit: 'day'
			                }
				      	}],
				      	// xAxes: [{
			        //         type: 'time',
			        //         position: 'bottom',
			        //         time: {
			        //             min: "2019-1-1",
			        //             max: "2019-2-28",
			        //             unit: "month",
			        //             displayFormats: {
			        //                 "month": "YYYY-MM",
			        //             }
			        //         }
			        //     }]
				      	yAxes: [{
				            ticks: {
				                beginAtZero: true,
				                // stepSize: 10
				            }
				        }]
				    },
				    layout: {
			            padding: {
			                left: 20,
			                right: 30,
			                top: 10,
			                bottom: 10
			            }
			        },
			        legend: {
			            display: true,
			            position: 'right',
			            labels: {
			                fontColor: 'rgb(0, 0, 0)',
			                fontSize: 10,
			            	padding: 10,
			            },
			            title: {
				          display: true,
				          fontSize: 10,
				          text: 'Product',
				        }
			        },
			        title: {
			            display: true,
		                fontSize: 20,
			            text: chart_title
			        },
				  }
				});
	    }





    // var start = moment().subtract(29, 'days');
    // var end = moment();

    // function cb(start, end) {
    //     $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    // }

    // $('#reportrange').daterangepicker({
    //     startDate: start,
    //     endDate: end,
    //     ranges: {
    //        'Today': [moment(), moment()],
    //        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
    //        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
    //        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
    //        'This Month': [moment().startOf('month'), moment().endOf('month')],
    //        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
    //     }
    // }, cb);

    // cb(start, end);




	}
	return {
		init: init
	}
})();

$(function () { 
    var scope = $(document).find('body > div.wrapper > div.content-wrapper > section.content > div');
    if(scope.hasClass("dashboard-index")) {
    	 _IP.Dashboard.init();
       
    } else if(scope.hasClass("kbn-index")) {
    	 _IP.Kbn.init();
       
    } 
});