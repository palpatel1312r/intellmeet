import { Head } from "@inertiajs/react";
import { useEffect, useState } from "react";
import axios from "axios";

export default function Dashboard() {
    const [stats, setStats] = useState({
        totalMeetings: 0,
        pendingTasks: 0,
        upcomingMeetings: [],
        recentActivity: [],
    });

    useEffect(() => {
        fetchStats();
    }, []);

    const fetchStats = async () => {
        try {
            const response = await axios.get("/api/dashboard/stats");
            setStats(response.data);
        } catch (error) {
            console.error("Failed to fetch stats", error);
        }
    };

    return (
        <>
            <Head title="Dashboard" />
            <div className="px-4 sm:px-6 lg:px-8">
                <h1 className="text-2xl font-semibold text-gray-900">
                    Dashboard
                </h1>

                {/* Stats Grid */}
                <div className="mt-4 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div className="bg-white overflow-hidden shadow rounded-lg">
                        <div className="p-5">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <VideoCameraIcon className="h-6 w-6 text-gray-400" />
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Total Meetings
                                        </dt>
                                        <dd className="text-3xl font-semibold text-gray-900">
                                            {stats.totalMeetings}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white overflow-hidden shadow rounded-lg">
                        <div className="p-5">
                            <div className="flex items-center">
                                <div className="flex-shrink-0">
                                    <ClipboardDocumentListIcon className="h-6 w-6 text-gray-400" />
                                </div>
                                <div className="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt className="text-sm font-medium text-gray-500 truncate">
                                            Pending Tasks
                                        </dt>
                                        <dd className="text-3xl font-semibold text-gray-900">
                                            {stats.pendingTasks}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Upcoming Meetings */}
                <div className="mt-8">
                    <h2 className="text-lg font-medium text-gray-900">
                        Upcoming Meetings
                    </h2>
                    <div className="mt-4 bg-white shadow overflow-hidden sm:rounded-md">
                        <ul className="divide-y divide-gray-200">
                            {stats.upcomingMeetings.map((meeting) => (
                                <li key={meeting.id}>
                                    <div className="px-4 py-4 sm:px-6">
                                        <div className="flex items-center justify-between">
                                            <p className="text-sm font-medium text-indigo-600 truncate">
                                                {meeting.title}
                                            </p>
                                            <div className="ml-2 flex-shrink-0 flex">
                                                <p className="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    {meeting.status}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="mt-2 sm:flex sm:justify-between">
                                            <div className="sm:flex">
                                                <p className="flex items-center text-sm text-gray-500">
                                                    {new Date(
                                                        meeting.start_time,
                                                    ).toLocaleString()}
                                                </p>
                                            </div>
                                            <div className="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                                <Link
                                                    href={`/meetings/${meeting.id}/join`}
                                                    className="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Join Meeting
                                                </Link>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </>
    );
}
