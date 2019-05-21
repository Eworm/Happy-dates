<script>

export default {
   props: [],

    data: function() {
        return {}
    },

    methods: {

        deleteFeed: function(feed) {
            var self = this;

            swal({
                title: translate('cp.are_you_sure'),
                text: translate_choice('cp.confirm_delete_items', 1),
                type: 'warning',
                confirmButtonText: translate('cp.yes_im_sure'),
                showCancelButton: true,
                closeOnConfirm: false,
            },
            function(canDelete) {
                if (canDelete) {
                    self.$http.delete(
                        cp_url('addons/happydates/destroy'), {
                            feed: feed
                        },
                        function() {
                            location.reload();
                        }
                    )
                }
            });
        },

        refresh: function() {
            var self = this;
            self.$http.get(
                cp_url('addons/happy-dates/refreshAll')
            );
            setTimeout(function () {
                location.reload();
            }, 250);
        }
    },

    ready: function() {}
}

</script>
