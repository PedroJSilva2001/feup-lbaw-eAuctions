class Bid {
    constructor(id, user_id, auction_id, value, date){
        this.id = id;
        this.user_id = user_id;
        this.auction_id = auction_id;
        this.value = value;
        this.date = date;
    }

    static fromForm(auction_id, user_id, form){
        let value = form.querySelector('input[name="value"]').value;
        return new Bid(null, user_id, auction_id, value, null);
    }

    submit() {
        return api.post(`auctions/${this.auction_id}/bid`, {
            value: this.value
        });
    }

    static async submit(form, user_id, auction_id) {
        let bid = Bid.fromForm(auction_id, user_id, form);
        await bid.submit();
    }

    static async updateCurrentBid(auction_id){

        let maxBid = document.querySelector("#max-bid");
        let baseValue = document.querySelector("#base-value");

        let lastBidValue = maxBid ? parseFloat(maxBid.innerHTML) : parseFloat(baseValue.innerHTML);
        let bidPrimitive = await api.get(`auctions/${auction_id}/bid/current`).then(response => response.json());

        if (Object.keys(bidPrimitive).length === 0) return;

        let bid = new Bid(bidPrimitive['id'], bidPrimitive['user_id'], bidPrimitive['auction_id'], parseFloat(bidPrimitive['value'], bidPrimitive['date']));

        if (bid.value > lastBidValue) {

            if (maxBid != null) maxBid.innerHTML = `${bid.value}€`;
            else document.querySelector("#first-bid").innerHTML = `${bid.value}€`;

            let bidInput = document.querySelector("#bid-on");

            if (bidInput != null) {
                bidInput.min = bid.value+1;
                bidInput.placeholder = bid.value+1;
                bidInput.value = "";
            }
        }
        location.reload();
    }
}