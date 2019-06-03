After installing Happy Dates you'll see a new menu-item in Tools called 'Ical Sync'.

* [Adding a feed](#adding)
* [Edit feed settings](#edit)
* [Updates](#updates)
* [Events](#events)
* [Showing events](#list)
* [Info & tags](#detail)

## Adding a new feed <a id="adding"></a>
Click the 'Add a feed' button. Add the necessary info and click save.

Some settings are set to as a standard value:

* Feed updates are enabled.
* Scheduling is set to 60 minutes.
* New events are set to publish and use the 'Blog' collection.
* The event info is saved to preset fieldnames. You can choose to change them.

### Happy Dates ❤ Prestige Worldwide
Using the field presets means the info will be saved _the same way_ [Prestige Worldwide](https://statamic.com/marketplace/addons/prestige-worldwide) does. Happy Dates works without HP, but installing HP too means you'll be able to see event info and add manual events.

### About the from field/custom/disabled fieldtype
You'll see a lot of fieldtypes with a select input next to a text/suggest fieldtype. You can use these fieldtypes to map content from the feed events to your statamic entries. Since there's no way to check the feed for available content you'll just have to check the feed yourself to see which info you can work with.

## Edit feed settings <a id="edit"></a>
Clicking on a feed will go to the edit page. The settings are split up into different tabs to make it easier to manage the different settings.

### Settings
An overview of the general feed settings.

### Content
Choose what to do with the event info.

### Custom taxonomies & terms
You can enable and add custom terms to every new entry from a feed.

## Updates <a id="updates"></a>
Happy Dates uses the `php please happydates:refresh` task to check feeds for new events. You'll have to add the cron to your server if you want to automate this task. [More info on how to is here](https://docs.statamic.com/addons/classes/tasks).

You can also call the task manually from the command line (or press the 'Refresh all' button in the cp). Happy Dates will overwrite existing entries if the event in the feed is changed (it checks for a higher sequence), so beware of changing syndicated entries. The start date and title of an event are used to generate a filename.


## Events <a id="events"></a>
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

## Showing a list of events <a id="list"></a>
Use the following tags for a list of events. HP adds custom filters to a Statamic collection, the rest is pure Statamic. [More info about collections is here](https://docs.statamic.com/tags/collection).

### Get all future events <a id="future"></a>
In this example I'm using the Redwood partial 'block', but you'll probably want to change that to your own partial. Remove `paginate="true" limit="10" as="hp_events"`, `{{ hp_events }}`, and `{{ partial:pagination }}` if you don't care about pagination. Change `collection:blog` to the collection you use for your events.

    {{ collection:blog filter="happy_dates" remove="past" paginate="true" limit="10" as="hp_events" }}
        {{ hp_events }}
            {{ partial:block }}
            {{ partial:pagination }}
        {{ /hp_events }}
    {{ /collection:blog }}

### Get all past events <a id="past"></a>
    {{ collection:blog filter="happy_dates" remove="future" paginate="true" limit="10" as="hp_events" }}
        {{ hp_events }}
            {{ partial:block }}
            {{ partial:pagination }}
        {{ /hp_events }}
    {{ /collection:blog }}

## Detail page info & tags <a id="detail"></a>
The idea of HP is to give you the freedom to build your event page the way you want to. You can use the following variables and tags:

### Variables
* Start date: `{{ pw_start_date }}`
* End date: `{{ pw_end_date }}`

### Recurring dates
<table>
    <tbody>
        <tr>
            <td>Get</td>
            <td>`{{ happy_dates:recurring }}{{ /happy_dates:recurring }}`</td>
            <td>Returns an array</td>
        </tr>
    </tbody>
</table>

**Example**   

    {{ if pw_recurring }}
        <ul>
            {{ happy_dates:recurring }}
            <li>{{ start format="j F Y, G:i" }} - {{ end format="j F Y, G:i" }}</li>
            {{ /happy_dates:recurring }}
        </ul>
    {{ /if }}

If you install [Prestige Worlwide](https://statamic.com/marketplace/addons/prestige-worldwide) too you can use the [PW tags](https://statamic.com/marketplace/addons/prestige-worldwide/docs#tags) to display HP info!

### One calendar to rule them all
HP downloads all ical files and saves them in the Statamic cache when you hit refresh all. You can display a calendar of all events with this tag.
<table>
    <tbody>
        <tr>
            <td>Get</td>
            <td>`{{ happy_dates:calendar }}{{ /happy_dates:calendar }}`</td>
            <td>Returns an array</td>
        </tr>
    </tbody>
</table>

#### Parameters
<table>
<tbody>
<tr>
<td>feed</td>
<td>The feed name (slugified)</td>
</tr>
<tr>
<td>start</td>
<td>A date or just `now`</td>
</tr>
<tr>
<td>end</td>
<td>All options available as a [date modifier](https://docs.statamic.com/modifiers/modify_date)</td>
</tr>
</tbody>
</table>


**Example**   

    <ul>
        {{ happy_dates:calendar start=now end="+4 weeks" }}
        <li>
            <strong>{{ title }}</strong>
            <br>
            {{ start_date }} - {{ status }}
            <br>
            {{ location }}
        </li>
        {{ /happy_dates:calendar }}
    </ul>

If you install [Prestige Worlwide](https://statamic.com/marketplace/addons/prestige-worldwide) too you can use the [PW tags](https://statamic.com/marketplace/addons/prestige-worldwide/docs#tags) to display HP info!
