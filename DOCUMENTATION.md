After installing Happy Dates you'll see a new menu-item in Tools called 'Ical Sync'.

## Adding a new feed
Click the 'Add a feed' button. Add the necessary info and click save.

Some settings are set to as a standard value:

* Feed updates are enabled.
* Scheduling is set to 60 minutes.
* New events are set to publish and use the 'Blog' collection.
* The event info is saved to preset fieldnames. You can choose to change them.

### Happy Dates ❤ Prestige Worldwide
Using the field presets means the info will be saved the same way [Prestige Worldwide](https://statamic.com/marketplace/addons/prestige-worldwide) does. Happy Dates works without PW, but installing PW too means you'll be able to see event info and add manual events.

## About the from field/custom/disabled fieldtype
You'll see a lot of fieldtypes with a select input next to a text/suggest fieldtype. You can use these fieldtypes to map content from the feed events to your statamic entries. Since there's no way to check the feed for available content you'll just have to check the feed yourself to see which info you can work with.

## Edit feed settings
Clicking on a feed will go to the edit page. The settings are split up into different tabs to make it easier to manage the different settings.

### Settings
An overview of the general feed settings.

### Content
Choose what to do with the event info.

### Custom taxonomies & terms
You can enable and add custom terms to every new entry from a feed.

## Updates
Happy Dates uses the `php please happydates:refresh` task to check feeds for new events. You'll have to add the cron to your server if you want to automate this task. [More info on how to is here](https://docs.statamic.com/addons/classes/tasks).

You can also call the task manually from the command line (or press the 'Refresh all' button in the cp). Happy Dates will overwrite existing entries if the event in the feed is changed (it checks for a higher sequence), so beware of changing syndicated entries. The start date and title of an event are used to generate a filename.


## Events
Happy Dates fires the `HappyDates:beforecreate` event right before creating an entry, and gives you the entry data, the collection and the option to cancel creating the entry. This is an example of the array:

```
{
    "collection": "happy days episodes",
    "entry": {
        "title": "All the Way"
        "pw_timezone": null
        "sequence": 0
        "pw_description": "The first episode"
        "pw_uid": bmgvih9idcuiet3rfc336dq6so@ical.com
        "pw_location": "Milwaukee, Wisconsin"
        "pw_status": "Aired"
        "pw_created": "2019-05-18 12:44:00"
        "pw_updated": "2019-05-22 19:44:28"
        "pw_start_date": "1975-04-02 20:00"
        "pw_end_date": "1975-04-02 20:30"
    },
    "create": true
}
```

Instead of filters in the control panel you can use this to change the collection, content or disable creating the entry by setting `create: false`;
