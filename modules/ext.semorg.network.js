$( '.semorg-network' ).each( function() {
	var id = $(this).attr('id');
	var network_data = window[id];
	var svg = d3.select("#" + id + " svg"),
		width = +svg.attr("width"),
		height = +svg.attr("height"),
		radius = 5;

	var g = svg.append("g")
		.attr("class","everything");

	//add zoom capabilities
	var zoom_handler = d3.zoom()
	    .on("zoom", zoom_actions);

	zoom_handler(svg);

	function zoom_actions(){
		g.attr("transform", d3.event.transform);
	}

	var color = d3.scaleOrdinal(d3.schemeCategory20);

	var simulation = d3.forceSimulation()
		.force("link", d3.forceLink().id(function(d) { return d.id; }))
		.force("charge", d3.forceManyBody().strength(-100).distanceMax(150).distanceMin(50))
		.force("center", d3.forceCenter(width / 2, height / 2));

	var link = g.append("g")
		.attr("class", "links")
		.selectAll("line")
		.data(network_data.links)
		.enter().append("line")
		.attr("stroke-width", function(d) { return Math.sqrt(d.value); });

	var gnodes = g.selectAll('g.gnode')
		.data(network_data.nodes)
		.enter()
		.append('g')
		.classed('gnode',true)
		.call(d3.drag()
			.on("start", dragstarted)
			.on("drag", dragged)
			.on("end", dragended)
		)
		.on("mouseover",mouseover)
		.on("mousemove",mousemove)
		.on("mouseout",mouseout)
		.on("click",function(d) {
			window.location = '/wiki/' + d.id;
		});

	var node = gnodes.append("circle")
		.attr("r", radius )
		.attr("class", function(d) { return 'semorg-network-node-' + d.category; });

	var labels = gnodes.append('text')
		.text(function(d) { 
			return ( d.group % 2 == 1 ) ? d.text : d.text; 
		} )
		.attr("text-anchor", "middle")
		.attr("dy", "-.5em");

	var tooltip = d3.select("body").append("div")
		.attr( "class", "network-tooltip" )
		.style( "display", "none" );

	simulation
		.nodes(network_data.nodes)
		.on("tick", ticked);

	simulation.force("link")
		.links(network_data.links);

	function ticked() {
		link
			.attr("x1", function(d) { return d.source.x; })
			.attr("y1", function(d) { return d.source.y; })
			.attr("x2", function(d) { return d.target.x; })
			.attr("y2", function(d) { return d.target.y; });
		/* node
			.attr("cx", function(d) { return d.x = Math.max(radius, Math.min(width - radius, d.x)); })
			.attr("cy", function(d) { return d.y = Math.max(radius, Math.min(height - radius, d.y)); });
			*/

		gnodes.attr("transform", function(d) {
			return 'translate(' + [d.x, d.y] + ')';
		});
	}

	function dragstarted(d) {
		if (!d3.event.active) simulation.alphaTarget(0.3).restart();
		d.fx = d.x;
		d.fy = d.y;
	}

	function dragged(d) {
		d.fx = d3.event.x;
		d.fy = d3.event.y;
	}

	function dragended(d) {
		if (!d3.event.active) simulation.alphaTarget(0);
		d.fx = null;
		d.fy = null;
	}

	function mouseover() {
		tooltip.style("display", "inline");
	}

	function mousemove(d) {
		var height = $('.network-tooltip').height();
		tooltip
			.text(d.tooltip)
			.style("left", (d3.event.pageX - 75) + "px")
			.style("top", (d3.event.pageY - 5 - height) + "px");
	}

	function mouseout() {
		tooltip.style("display", "none");
	}
});
