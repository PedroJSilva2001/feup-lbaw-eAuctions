class Timer {
    construtor(){
        this.timer = null;
    }

    start(date, id){
        this.timer = setInterval(() => this.setTimer(date, id), 1000);
    }

    setTimer(date, id){
        
        var endsIn = new Date(date).getTime();
        var now = new Date().getTime();

        var distance = endsIn - now;

        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        if(document.getElementById(id) != null){
            document.getElementById(id).innerHTML = " " + days.toString() + "d " + hours.toString() + "h "
            + minutes.toString() + "m " + seconds.toString() + "s ";      
        }
    }
}
