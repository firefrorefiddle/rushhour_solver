function mkBoard(sel_div, xdim, ydim, scale) {
    div = $(sel_div);
    div.css("width", scale*xdim);
    div.css("height", scale*ydim);
    div.css("border", "1px solid black");
    div.css("background-image", "url('square.png')");
    div.css("background-repeat", "repeat");
    div.css("background-size", scale);
    div.css("position", "relative");    
    return div;
}

function solverUrl (query) {
    return "http://localhost:4123/solver.php?query="+query;
}

class Game {
    
    constructor(xdim, ydim, cars) {
        this.xdim = xdim;
        this.ydim = ydim;
        this.cars = cars;
    }

    rotateOffset(car) {
        return car.orientation == "y" ? 0 : this.scale * (1/(4-car.size));
    }
    
    xpx(car) {
	var pos = car.orientation === "x" ? car.pos : car.x;
        return this.scale*(pos-1) + this.rotateOffset(car);
    }

    ypx(car) {
	var pos = car.orientation === "y" ? car.pos : car.y;	
        return this.scale*(pos-1) - this.rotateOffset(car);
    }

    carstyle(car) {
        var style = `width:${this.scale}px;position:absolute;left:${this.xpx(car)}px;top:${this.ypx(car)}px;`;
        if(car.orientation === "x") {
            style += "transform:rotate(90deg)";
	}
	return style;
    }
    
    render(sel_div, scale) {
        this.board = mkBoard(sel_div, this.xdim, this.ydim, scale);
        this.scale = scale;
	var i=0;
        this.cars.forEach(car => {
	    car.pos = car.orientation === "x" ? car.x : car.y;
	    car.fullPos = car.pos;
            var img = $("<img>");
	    if(car.id === "red") {
		car.color = [255, 0, 0];		
	    } else {
		car.color = [Math.round(Math.random()*255),Math.round(Math.random()*255),Math.round(Math.random()*255)];
	    }
            img.attr("src", `car_img.php?size=${car.size}&R=${car.color[0]}&G=${car.color[1]}&B=${car.color[2]}`);
	    img.attr("style", this.carstyle(car));	    
            this.board.append(img);
            car.repr = img;	    
        });
        
        return this;
    }

    carById(id) {
	var retval;
        this.cars.forEach(car => {
            if(car.id === id) {
                retval = car;		
            }
        });
	return retval;
    }
                          
    moveCar(id, dest) {
        var car = this.carById(id);
        if(car) {
	    car.pos = dest;
	    if(Math.abs(Math.round(car.pos) - car.pos) == 0) {
		car.fullPos = dest;
	    }
	    car.repr.attr("style", this.carstyle(car));
        }
    }

    findPlan() {
	var plan;
	var game = this;
	
	function encodeJson(game) {
	    var encoding = {
		"cars": []
	    };
	    game.cars.forEach(car => {
		encoding.cars.push([car.id, car.x, car.y, car.orientation, car.size]);
	    });
	    return encoding;
	}
	$.getJSON(solverUrl(JSON.stringify(encodeJson(this))), resp =>
		  {
		      game.plan = new Plan (game, resp);
		      game.plan.startAnimation(30, 500);
		      return game.plan;
		  });
    }
};

class Plan {
    constructor(game, moves) {
        this.game = game;
	this.moves = moves;
    }

    startAnimation(ticksPerMove, msPerMove) {
	this.currentTick = 0;
	this.ticksPerMove = ticksPerMove;
	this.msPerMove = msPerMove;
	this.running = true;
	this.tick();
    }

    tick() {
	var currentMove = this.moves[Math.floor(this.currentTick / this.ticksPerMove)+1];
	if(this.running && currentMove) {
	    currentMove.forEach(action => {
		var id = action[0];
		var car = this.game.carById(id);
		var dest = action[1];
		if(car) {
		    var progress = (this.currentTick % this.ticksPerMove) * 1.0 / this.ticksPerMove;
		    if((this.currentTick+1) % this.ticksPerMove == 0) {
			progress = 1;
		    }
		    console.log("progress from %d to %d:  %.2f", car.fullPos, car.dest, progress);
		    if(car.fullPos > dest) {
			this.game.moveCar(id, car.fullPos-progress);
		    } else {
			this.game.moveCar(id, car.fullPos+progress);
		    }
		}
	    });
	    this.currentTick++;
	    var that = this;
	    window.setTimeout(function(e) { that.tick();} , this.msPerMove/this.ticksPerMove);
	}	
    }
};
