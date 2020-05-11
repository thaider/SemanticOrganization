$( '.semorg-circles' ).each( function() {
	var id = $(this).attr('id');
	var root = window[id];
	var svg = d3.select("#" + id + " svg"),
		diameter = Math.min( +svg.attr("width"), +svg.attr("height") ),
		g = svg.append("g").attr("transform", "translate(2,2)"),
		format = d3.format(",d");

	var pack = d3.pack()
		.size([diameter - 4, diameter - 4]);

	var root = d3.hierarchy(root)
		.sum(function(d) { return d.size; })
		.sort(function(a, b) { return b.value - a.value; });

	var node = g.selectAll(".node")
		.data(pack(root).descendants())
		.enter().append("g")
		.attr("class", function(d) { 
			var classes = [ "node", d.data.type ];
			if( !d.children ) classes.push( "leaf" );
			if( d.data.group_member != d.data.group_role ) classes.push( "external" );
			return classes.join(" ");
		})
		.attr("transform", function(d) { return "translate(" + d.x + "," + d.y + ")"; });

	node.append("title")
		.text(function(d) { return d.data.name + "\n" + format(d.value) + 'h'; });

	node.append("circle")
		.attr("r", function(d) { return d.r; });

	node.filter(function(d) { return !d.children && d.data.type == "role"; })
		.on("click", function(d) { d3.event.stopPropagation(); window.location = '/wiki/' + d.data.link; })
		.append("text")
		.attr("dy", "0.3em")
		.attr("class", function(d) { return "role" + ( ( d.link == d.group ) ? " external" : "" ); })
		.text(function(d) { return d.data.name.substring(0, d.r / 3); });

	node.filter(function(d) { return (d.data.type == "member"); })
		.append("text")
		.attr("dy", "0.3em")
		.attr("class", "member")
		.text(function(d) { return d.data.name; });

	/*
	node.filter(function(d) { return (d.data.type == "group"); })
		.append("text")
		.attr("dy", function(d) { return d.r + 10; })
		.attr("class", "group")
		.text(function(d) { return d.data.name; });
	*/

	svg.on("click", function() { $( '.role, text.member' ).toggle(); });
});
